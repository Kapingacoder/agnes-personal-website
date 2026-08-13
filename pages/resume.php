<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="page resume-page">
  <div class="container">
    <header class="page-header">
      <h2 class="section-title">Resume</h2>
      <p class="section-sub">Curriculum Vitae — academic and professional overview</p>
    </header>

    <section class="section about-me">
      <h3>About Me</h3>
      <div class="expandable" data-collapsed-lines="6">
        <p>
          I am Dr. Agnes Gisbert Kapinga, a Lecturer and Researcher at the Tengeru Institute of Community Development in Arusha, Tanzania. With over six years of professional experience, my work focuses on climate resilience, environmental governance, social justice, and sustainable livelihoods.
        </p>
        <p>
          I am deeply committed to advancing community driven approaches to environmental management bridging research, policy, and practice to strengthen resilience among vulnerable groups, especially youth and women. My academic and field experience span climate change adaptation, nature based solutions, and community based natural resource management, supported by a strong background in environmental economics and management.
        </p>
        <p>
          I hold a Ph.D. in Environmental Management from the University of Ibadan (Nigeria), a Master’s in Environmental and Natural Resource Economics, and a Bachelor’s degree in Environmental Sciences and Management from Sokoine University of Agriculture (Tanzania).
        </p>
        <p>
          Beyond academia, I have served as a consultant, advisor, and facilitator on various national and international projects with organizations such as ActionAid Tanzania, SNV, UNEP, The Nature Conservancy, and Mott MacDonald. My work emphasizes participatory approaches, gender inclusion, and policy integration in climate action.
        </p>
        <p>
          My research has been published in reputable international journals, including Habitat International and Water Science, reflecting my passion for linking scientific evidence with community realities.
        </p>
        <p>
          When I’m not in the classroom or field, I enjoy traveling, athletics, and home gardening activities that connect me to the very ecosystems I study and teach about.
        </p>
      </div>
      <button class="see-more-toggle btn btn-secondary" aria-expanded="false">See More</button>
    </section>

    <section class="section education">
      <h3>Education</h3>
      <ul class="edu-list">
        <li>
          <strong>Bachelor's Degree</strong><br>
          Environmental Sciences and Management<br>
          Sokoine University of Agriculture, Tanzania
        </li>
        <li>
          <strong>Master's Degree</strong><br>
          Environment and Natural Resource Economics<br>
          Sokoine University of Agriculture, Tanzania
        </li>
        <li>
          <strong>PhD</strong><br>
          Environmental Management<br>
          University of Ibadan, Nigeria
        </li>
        <li>
          <strong>Post-doctorate</strong><br>
          Urban Sustainability<br>
          University of Pretoria, South Africa
        </li>
      </ul>
    </section>

    <section class="section professional-overview">
      <h3>Professional Overview</h3>
      <div class="expandable" data-collapsed-lines="4">
        <p>
          <!-- Replace the content below with your official professional overview. -->
          I have worked across academia and consultancy, leading research and applied projects on climate resilience, policy integration, and community-driven natural resource management. I design participatory approaches that mainstream gender, youth engagement, and ecosystem-based solutions into local and national programs.
        </p>
        <p>
          My roles have included project design, monitoring and evaluation, stakeholder facilitation, and technical advisory for NGOs and intergovernmental organizations. I bridge disciplinary research with practical implementation to drive equitable and sustainable outcomes.
        </p>
        <p>
          I also mentor students and early-career researchers, supervise theses, and contribute to curriculum development in environmental management and sustainability studies.
        </p>
      </div>
      <button class="see-more-toggle btn btn-secondary" aria-expanded="false">See More</button>
    </section>

    <section class="section vision-mission">
      <div class="grid">
        <div>
          <h4>Vision</h4>
          <p>To be a catalyst for sustainable transformation, where people and nature thrive together in balance and mutual respect.</p>
        </div>
        <div>
          <h4>Mission</h4>
          <p>To promote responsible development by integrating human needs with the protection of the natural environment through innovation, partnership, and education.</p>
        </div>
      </div>
    </section>

    <section class="section values">
      <h3>Core Values</h3>
      <ul class="values-list">
        <li>Sustainability</li>
        <li>Integrity</li>
        <li>Collaboration</li>
        <li>Innovation</li>
        <li>Respect</li>
      </ul>
    </section>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
