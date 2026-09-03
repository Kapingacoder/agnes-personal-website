document.addEventListener('DOMContentLoaded', function () {
  if (!window.pdfjsLib) return;
  window.pdfjsLib.GlobalWorkerOptions.workerSrc = '/assets/vendor/pdfjs/pdf.worker.min.js';

  document.querySelectorAll('.journal-reader').forEach(function (reader) {
    var canvas = reader.querySelector('.journal-reader__canvas');
    var viewport = reader.querySelector('.journal-reader__viewport');
    var counter = reader.querySelector('[data-role="page-counter"]');
    var documentProxy;
    var pageNumber = 1;
    var scale = 1.1;
    var pageCount = 1;
    var renderTask;

    function renderPage() {
      if (!documentProxy) return;
      viewport.classList.add('is-loading');
      if (renderTask) renderTask.cancel();
      documentProxy.getPage(pageNumber).then(function (page) {
        var pageViewport = page.getViewport({ scale: scale });
        canvas.width = pageViewport.width;
        canvas.height = pageViewport.height;
        renderTask = page.render({ canvasContext: canvas.getContext('2d'), viewport: pageViewport });
        return renderTask.promise;
      }).then(function () {
        counter.textContent = 'Page ' + pageNumber + ' of ' + pageCount;
        viewport.classList.remove('is-loading');
      }).catch(function (error) {
        if (error && error.name !== 'RenderingCancelledException') counter.textContent = 'Unable to render page';
        viewport.classList.remove('is-loading');
      });
    }

    reader.querySelectorAll('[data-action]').forEach(function (button) {
      button.addEventListener('click', function () {
        var action = button.dataset.action;
        if (action === 'prev') pageNumber = Math.max(1, pageNumber - 1);
        if (action === 'next') pageNumber = Math.min(pageCount, pageNumber + 1);
        if (action === 'zoom-in') scale = Math.min(2.5, scale + 0.2);
        if (action === 'zoom-out') scale = Math.max(0.8, scale - 0.2);
        if (action === 'print' && reader.dataset.allowPrint === '1') window.print();
        if (action === 'fullscreen') {
          if (!document.fullscreenElement && reader.requestFullscreen) reader.requestFullscreen();
          else if (document.exitFullscreen) document.exitFullscreen();
        }
        if (['prev', 'next', 'zoom-in', 'zoom-out'].indexOf(action) !== -1) renderPage();
      });
    });

    window.pdfjsLib.getDocument(reader.dataset.pdfUrl).promise.then(function (pdf) {
      documentProxy = pdf;
      pageCount = pdf.numPages || 1;
      renderPage();
    }).catch(function () {
      counter.textContent = 'Journal unavailable';
      viewport.classList.remove('is-loading');
    });
  });
});
