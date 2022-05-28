<?php
  ob_start();
  require_once(dirname(__FILE__).'/include/load.php');
  require_once(dirname(__FILE__).'/data.php');

  /* load data on page reloading */
  $path = './data/FakeSample.json';         // <-- datafile name
  $data = parse($path);
  if (!$data) { 
    die("<h2>Ups! Unable to read data from file '${path}'. Badformed, or it does not exist.<h2>"); 
  }
  clearData();      // clean DB
  saveDB($data);    // store info from file

  /* return JSON-encoded data, read from the DB (not the file) */
  $data = readDB();
  $data = $data[0];       // take the first element only
?>

<?php
  /* get data-action attribute for table */
  $data_action = "";
  if (strtoupper($data['Action']) == "CAUTION") 
    $data_action = "caution";
  else if (strtoupper($data['Action']) == "GO") 
    $data_action = "go";
?>

<?php include_once('layouts/header.php'); ?>

<div class="text-center">
  <h1>Simple WebApp, Fake Sample</h1>
  <h3>Datafile is: <b>data/FakeSample.json</b></h3>
</div>

<!-- Why using HTML <table> instead of div's to build a tabular data?
     Because that is the correct option.
     See: https://stackoverflow.com/questions/3053205/how-create-table-only-using-div-tag-and-css
-->
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span id="table-title"><?php echo($data['TherapeuticArea']);?></span>    <!-- load dynamically with PHP embedded code-->
        </strong>
      </div>
    <div class="panel-body">
      <table class="table table-drug table-responsive-md" data-action="<?php echo "${data_action}";?>" id="table-drug" data-hidden="true">
        <!-- column layout -->
        <col id="col-drug" class="col-drug">                 <!-- column 1 -->
        <col id="col-gene" class="col-gene" />               <!-- column 2 -->
        <col id="col-genotype" class="col-genotype" />       <!-- column 3 -->
        <col id="col-phenotype" class="col-phenotype" />     <!-- column 4 -->
        <col id="col-recommendation" class="col-recommendation";/>   <!-- column 5 -->
        <tr>
          <td id="drug-name" class="col-drug" rowspan="4" style="width: 20%; vertical-align: middle;">
            <span id="drug-generic-name" class="drug-generic-name"><?php echo($data['DrugGeneric']);?></span>
            <br/>
            <span id="drug-trade-name" class="drug-trade-name">(<?php echo($data['DrugTrade']);?>)</span>
          </td>
          <!-- These are static column headers -->
          <th class="gene hiddenable">Gene</th>
          <th class="genotype hiddenable">Genotype</th>
          <th class="phenotype hiddenable">Phenotypes/Patient Impact</th>
          
          <td class="col-recommendation hiddenable" rowspan="4" style="vertical-align: middle;">
            <?php echo($data['Recommendation']);?>  <!-- feed data dynamically -->
          </td>
        </tr>
        <!-- GeneInfo: this info is loaded dynamically -->
        <?php foreach ($data['GeneInfo'] as $ginfo): ?>
        <tr>
          <td class="gene hiddenable"><?php echo $ginfo['Gene'];?></td>
          <td class="genotype hiddenable"><?php echo $ginfo['Genotype'];?></td>
          <td class="phenotype hiddenable"><?php echo $ginfo['Phenotype'];?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
          <td id="drug-action" class="drug-action hiddenable" colspan="2"><?php echo($data['Action']);?></td>  <!-- feed data dynamically -->
          <td id="drug-group-phenotype" class="drug-group-phenotype hiddenable"><?php echo($data['GroupPhenotype']);?></td>  <!-- feed data dynamically -->
        </tr>
      </table>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>

<script type="text/javascript" src="<?php echo SITE_URL;?>/js/FakeSample.js"></script>