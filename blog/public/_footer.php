
  <!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<?php
include './../includes/db.php';
include_once __DIR__ . '/../includes/config.php';
include_once 'settings.php';
$conn = getDbConnection();
?>


<footer>
    <div class="bb ze ki xn 2xl:ud-px-0">


      <div class="seo-content">
        <div class="seo-text mt60">
         
          <p class="justify">
        Padmarajam Institute of Management (PRIM), founded by Mr. PA. Balan, Founder, Chairman and Managing Director, is one of Tamil Nadu’s leading professional coaching institutes for Accountancy and Management courses. Built on the belief that education can unlock potential and create opportunities for everyone, PRIM has been a trusted destination for aspiring students aiming to become successful CA, CMA, CS, and management professionals. With a vision of Creating Quality Professionals, PRIM offers the best combination of eminent faculty members, modern infrastructure, a conducive learning atmosphere, and a strong student-focused approach. Recognised for its consistent results and holistic training based on Knowledge, Skill and Attitude, PRIM stands as a top choice among students. Proudly, Padmarajam Institute of Management is the first institution in India to be recognised by the International Accreditation and Recognition Council (IAF), Australia, marking its excellence in global academic standards.




          </p>
        </div>


        


      <!-- Footer Top -->
      <div class="ji gp">
        <div class="tc uf ap gg fp">
         

          <div class="vd ro tc sf rn un gg vn">

             <div class="animate_top footerLogo">
            <a href="index.html">
               <img class="om" src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" />
               <img class="xc nm" src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" />
            </a>

            <p class="lc fb mt-0"><?= htmlspecialchars($settings['company_name'] ?? 'Brandpreneuring') ?></p>
            <p>10, Kalpalam Road, Near Thever Statue to<br> Meenakshi Women's college, <br>Goripalayam, Madurai- 625002

</p>

            <ul class="tc wf cg mt20">

            <div class="social-links d-flex">
           
            <a href="https://wa.me/917824098845?text=Hello,%20We%20Came%20Across%20MGR%20Biriyani%20Kadai." class="twitter" target="_blank">
              <i class="fab fa-whatsapp"></i>
            </a>
            <a href="https://www.youtube.com/" class="youtube"><i class="fab fa-youtube"></i></a>
            <a href="https://www.facebook.com/padmarajamcollegeofmanagement/" class="facebook"><i class="fa-brands fa-facebook"></i></a>
            <a href="https://www.instagram.com/prim_acca_ca/" class="instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://www.linkedin.com/" class="linkedin"><i class="fab fa-linkedin"></i></a>
            <a href="https://x.com/" class="twitter"><i class="fab fa-x-twitter"></i></a>

          </div>


            
              
            </ul>
          </div>

            <div class="animate_top">
              <h4 class="kk wm tj ec">Quick Links</h4>

               

              <ul>
                <li><a href="https://www.padmarajam.org/" class="sc xl vb">Home</a></li>
                <li><a href="https://www.padmarajam.org/#about-section" class="sc xl vb">About Us</a></li>
                <li><a href="https://www.padmarajam.org/courses" class="sc xl vb">Courses</a></li>
                <li><a href="https://www.padmarajam.org/blog" class="sc xl vb">Blogs</a></li>
                <li><a href="https://www.padmarajam.org/#gallery-section" class="sc xl vb">Gallery</a></li>
                <li><a href="https://www.padmarajam.org/#contact" class="sc xl vb">Contact</a></li>
               
              </ul>
            </div>

            <div class="animate_top">
              <h4 class="kk wm tj ec">Contact</h4>

              <ul>
                <li>Phone: <a href="tel:+918122247567" class="sc xl vb">+91  812 224 7567</a></li>
                <li>Email: <a href="mailto:padmarajam@gmail.com" class="sc xl vb">padmarajam@gmail.com</a></li>
              </ul>

            

             
            </div>

           

           
          </div>
        </div>
      </div>
      <!-- Footer Top -->

      <!-- Footer Bottom -->
      <div class="bh ch pm tc uf sf yo wf xf ap cg fp bj">
        <div class="animate_top">
          <ul class="tc wf gg">
            <li><a href="#!" class="xl">English</a></li>
            <li><a href="#!" class="xl">Privacy Policy</a></li>
            <li><a href="#!" class="xl">Support</a></li>
          </ul>
        </div>

        <div class="animate_top">
          <p>© 2025 <?= htmlspecialchars($settings['company_name'] ?? 'Brandpreneuring') ?>. All Rights Reserved.</p>
        </div>
      </div>
      <!-- Footer Bottom -->
    </div>
  </footer>



  <!-- ====== Back To Top Start ===== -->
  <button class="xc wf xf ie ld vg sr gh tr g sa ta _a" @click="window.scrollTo({top: 0, behavior: 'smooth'})"
    @scroll.window="scrollTop = (window.pageYOffset > 50) ? true : false" :class="{ 'uc' : scrollTop }">
    <svg class="uh se qd" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
      <path
        d="M233.4 105.4c12.5-12.5 32.8-12.5 45.3 0l192 192c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L256 173.3 86.6 342.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l192-192z" />
    </svg>
  </button>

  <!-- ====== Back To Top End ===== -->

</body>

<?php
// Compute project root (one level above the public/ folder) so script URL works from any page
$projectRoot = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$bundleUrl = $projectRoot . '/assets/frontend/js/bundle.js';
?>



  <script defer src="<?= htmlspecialchars($bundleUrl) ?>"></script>
</html>
</body>

</html>