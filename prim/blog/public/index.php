
<?php 
include '_header.php';
?>

 <main>
    <!-- ===== Blog Grid Start ===== -->
    <section class="ji gp uq mainSection">
      <div class="bb ze ki xn vq jb jo">

       




             <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <h2 class="text-2xl font-semibold text-gray-700">All Blogs</h2>


             <div class="jn/2 so">
            <div class="animate_top">
              <form action="#">
                <div class="i">
                    <input 
                    type="text" 
                    id="searchBox" 
                    placeholder="Search Blogs..." 
                    class="vd sm _g ch pm vk xm rg gm dm/40 dn/40 li mi"
                    value="<?= $initialSearch ?: $initialTag ?>"    
                />

                  <button class="h r q _h" disabled>
                    <svg class="th ul ml il" width="21" height="21" viewBox="0 0 21 21" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M16.031 14.617L20.314 18.899L18.899 20.314L14.617 16.031C13.0237 17.3082 11.042 18.0029 9 18C4.032 18 0 13.968 0 9C0 4.032 4.032 0 9 0C13.968 0 18 4.032 18 9C18.0029 11.042 17.3082 13.0237 16.031 14.617ZM14.025 13.875C15.2941 12.5699 16.0029 10.8204 16 9C16 5.132 12.867 2 9 2C5.132 2 2 5.132 2 9C2 12.867 5.132 16 9 16C10.8204 16.0029 12.5699 15.2941 13.875 14.025L14.025 13.875Z" />
                    </svg>
                  </button>
                </div>
              </form>
            </div>
            </div>

            <!-- <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
              
                <input 
                    type="text" 
                    id="searchBox" 
                    placeholder="Search blogs..." 
                    class="border border-gray-300 rounded-lg p-2 w-64 bg-white shadow-sm"
                    value="<?= $initialSearch ?: $initialTag ?>"
                />

              
                <select 
                    id="category" 
                    name="category" 
                    class="border border-gray-300 rounded-lg p-2 bg-white shadow-sm"
                >
                    <option value="all">All</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>"
                            <?= ($initialCategory === $cat['name']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div> -->
        </div>

        <!-- Blog Grid -->
        <!-- <div id="blogGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"> -->
            <!-- AJAX will load blogs here -->
        <!-- </div> -->

        <!-- Pagination -->


            <div id="blogGrid" class="wc qf pn xo zf iq">
            </div>
       
        <div id="pagination" class="flex justify-center mt-10 space-x-2"></div>

            
          </div>
        </div>

        
      </div>
    </section>
    <!-- ===== Blog Grid End ===== -->


     <section class="i pg gh ji">
    <!-- Bg Shape -->
    <?php
    // Ensure $projectRoot is available (header may have set it). Fall back to compute here.
    if (!isset($projectRoot)) {
      $projectRoot = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
    }
    $shapePath = $projectRoot . '/public/images/shape-16.svg';
    ?>
    <img class="h p q" src="<?= htmlspecialchars($shapePath) ?>" alt="Bg Shape" />

      <div class="bb ze i z-10 ki xn dr">
        <div class="tc uf sn tn un gg">
          <div class="animate_left to/2">
            <h2 class="fk vj zp pr lk ac">
              Join 5,000+ Students Shaping Their Future with PRIM.
            </h2>
            <p class="lk">
          Padmarajam Institute of Management (PRIM) has empowered thousands of students to build successful
careers in CA, CMA, CS, MBA, and various management programs.
            </p>
          </div>
          <div class="animate_right bf">
            <a href="https://wa.me/918122247567?text=Can I get more details!"  target="_blank" class="vc ek kk hh rg ol il cm gi hi">
              Enroll Now
            </a>
          </div>
        </div>
      </div>
    </section>




  </main>




  
 <?php include '_footer.php'; ?>

    <!-- AJAX Script -->
    <script>
    function loadBlogs(page = 1) {
        const query = $('#searchBox').val();
        const tag = "<?= $initialTag ?>"; // preserve tag from URL if exists
        const category = $('#category').val();

        $.ajax({
            url: 'search_blogs.php',
            type: 'GET',
            data: {
                search: query,
                tag: tag,
                category: category,
                page: page
            },
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    $('#blogGrid').html(data.html);
                    $('#pagination').html(data.pagination);
                } catch (e) {
                    console.error("Invalid response:", response);
                }
            }
        });
    }

    // Initialize with URL parameters (on page load)
    $(document).ready(function() {
        loadBlogs();

        // Live search and filter event listeners
        $('#searchBox').on('keyup', function() { loadBlogs(1); });
        $('#category').on('change', function() { loadBlogs(1); });

        // Pagination click
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            loadBlogs(page);
        });
    });
    </script>
