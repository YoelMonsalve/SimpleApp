        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Author: Yoel Monsalve | <i>This is a Sample for a test</i>.</span>
          </div>
        </div>
      </footer>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <!--
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>-->

  <!-- Bootstrap core JavaScript-->
  <!--<script src="vendor/jquery/jquery.min.js"></script>-->
  <script src="<?php echo SITE_URL;?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="<?php echo SITE_URL;?>/vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="<?php echo SITE_URL;?>/js/sb-admin-2.min.js"></script>

  <!-- Page level plugins -->
  <!--<script src="vendor/chart.js/Chart.min.js"></script>-->

  <!-- Page level custom scripts -->
  <!--<script src="<?php echo SITE_URL;?>/js/demo/chart-area-demo.js"></script>
  <script src="<?php echo SITE_URL;?>/js/demo/chart-pie-demo.js"></script>-->

  <!--
  <script src="https://ajax.googleapis.com/ajax/lib/jquery/3.4.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/lib/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.min.js"></script>-->

  <!-- jquery/ajax -->
  <!-- === NOTE ===
    -- This was moved to the header.
    -- NOT working when placed into footer
    -- (don't know why ?)
  -->
  <!--<script type="text/javascript" src="<?php echo SITE_URL;?>/js/jquery-3.5.1.js"></script>-->

  <!-- cached version -->
  <!--<script type="text/javascript" src="cache/js/bootstrap.min.js"></script>-->

  <!-- DataTables -->
  <script type="text/javascript" src="<?php echo SITE_URL;?>/lib/DataTables/datatables.min.js"></script>
  
  <!-- Load custom functions -->
  <script type="text/javascript" src="<?php echo SITE_URL;?>/js/functions.js"></script>

  <!-- Custom additional effects -->
  <script type="text/javascript" src="<?php echo SITE_URL;?>/js/custom.js"></script>

  </body>
</html>
  
<?php if(isset($db)) { $db->db_disconnect(); } ?>
