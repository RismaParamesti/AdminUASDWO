<?php
    $dbHost = "localhost";
    $dbDatabase = "projectuas";
    $dbUser = "root";
    $dbPasswrod = "";

    $mysqli = mysqli_connect($dbHost, $dbUser, $dbPasswrod, $dbDatabase);

    //QUERY CHART PERTAMA

    //query untuk tahu SUM(Amount) semuanya
    $sql = "SELECT sum(Amount) as tot from factpurchase";
    $tot = mysqli_query($mysqli,$sql);
    $tot_amount = mysqli_fetch_row($tot);

    //echo $tot_amount[0];

    //query untuk ambil penjualan berdasarkan kategori, query sudah dimodifikasi
    //ditambahkan label variabel DATA. (teknik gak jelas :D)

	$sql = "SELECT concat('name:',f.Name) as name, concat('y:', sum(fp.Amount)*100/" . $tot_amount[0] .") as y, concat('drilldown:', f.Name) as drilldown
            FROM dimshipmethod f
            JOIN factpurchase fp ON (f.ShipMethodID = fp.ShipMethodID)
            GROUP BY name
            ORDER BY y DESC"   ;         
            //echo $sql;
    $all_kat = mysqli_query($mysqli,$sql);
    
    while($row = mysqli_fetch_all($all_kat)) {
        $data[] = $row;
    }
    

    $json_all_kat = json_encode($data);
    
    //CHART KEDUA (DRILL DOWN)

    //query untuk tahu SUM(Amount) semua kategori
    $sql = "SELECT f.`Name` AS `Name`, sum(fp.Amount) as tot_kat
            FROM factpurchase fp
            JOIN dimshipmethod f ON (f.ShipMethodID = fp.ShipMethodID)
            GROUP BY `Name`";
    $hasil_kat = mysqli_query($mysqli,$sql);

    while($row = mysqli_fetch_all($hasil_kat)){
        $tot_all_kat[] = $row;
    }

    //print_r($tot_all_kat);
    //function untuk nyari total_per_kat 

    //echo count($tot_per_kat[0]);
    //echo $tot_per_kat[0][0][1];
    
    function cari_tot_kat($kat_dicari, $tot_all_kat){
       $counter = 0;
       // echo $tot_all_kat[0];
       while( $counter < count($tot_all_kat[0]) ){
            if($kat_dicari == $tot_all_kat[0][$counter][0]){
                $tot_kat = $tot_all_kat[0][$counter][1];
                return $tot_kat;
            }
            $counter++;        
       }
    }

    //query untuk ambil penjualan di kategori berdasarkan bulan (CLEAN)
    $sql = "SELECT f.`Name` AS `Name`, 
            t.tahun as tahun, 
            sum(fp.Amount) as pendapatan_kat
            FROM dimshipmethod f
            JOIN factpurchase fp ON (f.ShipMethodID = fp.ShipMethodID)
            JOIN dimtimeall t ON (t.TimeID = fp.TimeID)
            GROUP BY `Name`, tahun";
    $det_kat = mysqli_query($mysqli,$sql);
    $i = 0;
    while($row = mysqli_fetch_all($det_kat)) {
        //echo $row;
        $data_det[] = $row;
        
    }

    //print_r($data_det);

    //PERSIAPAN DATA DRILL DOWN - TEKNIK CLEAN  
    $i = 0;

    //inisiasi string DATA
    $string_data = "";
    $string_data .= '{name:"' . $data_det[0][$i][0] . '", id:"' . $data_det[0][$i][0] . '", data: [';


    // echo cari_tot_kat("Action", $tot_all_kat);
    foreach($data_det[0] as $a){
        //echo cari_tot_kat($a[0], $tot_all_kat);

        if($i < count($data_det[0])-1){
            if($a[0] != $data_det[0][$i+1][0]){
                $string_data .= '["' . $a[1] . '", ' . 
                    $a[2]*100/cari_tot_kat($a[0], $tot_all_kat) . ']]},';
                $string_data .= '{name:"' . $a[0] . '", id:"' . $a[0]    . '", data: [';
            }
            else{
                $string_data .= '["' . $a[1] . '", ' . 
                    $a[2]*100/cari_tot_kat($a[0], $tot_all_kat) . '], ';
            }            
        }
        else{
            
                $string_data .= '["' . $a[1] . '", ' . 
                    $a[2]*100/cari_tot_kat($a[0], $tot_all_kat). ']]}';
               
        }
       
     
         $i = $i+1;
      
    }   



    $dataPoints = []; // Initialize empty array for data points

    // Query to get sales by vendor 
    $sqlQtyVendor = "   SELECT dp.VendorName AS VendorName, count(fs.Quantity) AS TotalQuantity
        FROM factpurchase fs
        JOIN vendor dp ON fs.VendorID = dp.VendorID 
        GROUP BY dp.VendorName";
    $resultQtyVendor = $mysqli->query( $sqlQtyVendor);
    
    // Check if there are results
    if ($resultQtyVendor && $resultQtyVendor->num_rows > 0) {
        // Initialize arrays to hold categories and data for the chart
        $categories = [];
        $data = [];
        
        // Loop through the results
        while ($row = $resultQtyVendor ->fetch_assoc()) {
            // Add data to points array for Highcharts
            $dataPoints[] = [
                "label" => $row['VendorName'],
                "y" => (float) $row['TotalQuantity']
            ];
            
            // Also populate categories and data arrays for the chart
            $categories[] = $row['VendorName']; // Product names
            $data[] = (float) $row['TotalQuantity'];     // Total sales (converted to float)
        }
        
        // Encode arrays to JSON for use in JavaScript
        $json_categories = json_encode($categories);
        $json_data = json_encode($data);
    }



    $query = "SELECT 
     sm.Name AS ShipMethod, 
     YEAR(dt.Tahun) AS PurchaseYear, 
     SUM(fp.Amount) AS TotalPurchase 
 FROM factpurchase fp
 JOIN dimshipmethod sm ON fp.ShipMethodID = sm.ShipMethodID 
 JOIN dimtimeall dt ON fp.TimeID = dt.TimeID
 GROUP BY sm.Name, YEAR(dt.Tahun)";

$result = mysqli_query($mysqli, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
 $data[] = $row;
}

// Format data untuk Chart.js
$chartData = [];
foreach ($data as $row) {
    $shipMethod = $row['ShipMethod'];
    $year = $row['PurchaseYear'];
    $purchase = $row['TotalPurchase'];

    if (!isset($chartData[$year])) {
        $chartData[$year] = [];
    }
    $chartData[$year][$shipMethod] = $purchase;
}

// Format data ke dalam label dan dataset
$shipMethods = [];
$years = array_keys($chartData);
$datasets = [];

foreach ($chartData as $year => $purchaseData) {
    foreach ($purchaseData as $shipMethod => $purchase) {
        if (!in_array($shipMethod, $shipMethods)) {
            $shipMethods[] = $shipMethod;
        }
    }
}
foreach ($years as $year) {
  $dataPoints = [];
  foreach ($shipMethods as $shipMethod) {
      $dataPoints[] = $chartData[$year][$shipMethod] ?? 0;
  }

  // Set warna berdasarkan tahun
  $color = ($year == 2011) ? 'red' : (($year == 2012) ? 'blue' : (($year == 2013) ? 'green' : (($year == 2014) ? 'yellow' : 'gray')));

  $datasets[] = [
      'label' => "Year $year",
      'data' => $dataPoints,
      'borderColor' => $color,
      'fill' => false
  ];
}



     
    //PERSIAPAN DASHBOARD ATAS (KOTAK)
    //1. Total Customer
    $sql2 = "SELECT count(distinct VendorName) as jml_cust from vendor";
    $jml_c = mysqli_query($mysqli,$sql2);
    $jml_cust = mysqli_fetch_assoc($jml_c);

    //2. Total Sales
    $sql3 = "SELECT sum(Amount) as tot2 from factpurchase";
    $tot2 = mysqli_query($mysqli,$sql3);
    $tot_penj = mysqli_fetch_assoc($tot2);

    //3. Total Judul Film
    $sql4 = "SELECT count(ProductID) as tot_jud_film from dimproduct";
    $tot3 = mysqli_query($mysqli,$sql4);
    $tot_jud_film = mysqli_fetch_assoc($tot3)

    
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AdminLTE 3 | Dashboard</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
  <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/drilldown.css"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js">
        
    </script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div>

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <!-- <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li> -->
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        
      </li>

      <!-- Messages Dropdown Menu -->
     
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">Risma & Septi</a>
        </div>
      </div>

      

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item menu-open">
          <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard 
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="./index.php" class="nav-link active">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard Sales</p>
                </a>
              </li>
              
            </ul>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="./dashboard-lain.php" class="nav-link active">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard Purchases</p>
                </a>
              </li>
              
            </ul>
          </li>
          
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="logout.php">Logout</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h2> <?php echo $jml_cust['jml_cust']; ?> </h2>

                <p><h3>Total Vendors</h3></p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
                
              </div>
              <!-- <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h2> <?php echo number_format($tot_penj['tot2'], 2, ',','.'); ?>   </h2>

                <p><h3>Total Purchases </h3></p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <!-- <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h2><?php echo $tot_jud_film['tot_jud_film']; ?></h2>

                <p><h3>Total Product</h3></p>
              </div>
              <div class="icon">
              <i class="ion ion-bag"></i>
              </div>
              <!-- <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
            </div>
          </div>
          
        </div>
        <!-- /.row -->
        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <section class="col-lg-7 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div>
              <div >
                
                
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content p-0">
                  <!-- Morris chart - Sales -->
                  <div  id="revenue-chart"
                       style="position: relative; height: 800px; width: 800px;">
                     <!-- <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas> -->
                     <figure class="highcharts-figure">
                      <div id="container"></div>
                      
                      <p class="highcharts-description">
                         
                      </p>
                      <div class="card">

                      <div class="card-body">
                      
    <div class="tab-content p-0">
        <!-- Chart Container -->
        <div id="revenue-chart" style="position: relative; height: 30px; width: 800px;">
            <figure class="highcharts-figure">
                <div id="container"></div>
            </figure>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Column Chart: Quantity by Vendor</h3>
        </div>
        <div class="card-body">
            <!-- Div for column chart -->
            <div id="bar-container" style="width:100%; height:400px;"></div>
        </div>
    </div>
    <div style="width: 80%; margin: auto;">
        <canvas id="salesChart"></canvas>
    </div>
                      <div>
                        <iframe name="mondrian" src="http://localhost:8080/mondrian/testpage.jsp?query=projectpurchase"  style="height:300px ;width:800px; border:none; text-align:center;"></iframe> 
                      </div>
                    </figure>

                    
                    
                    
                    <script type="text/javascript">
                    // Create the chart
                    Highcharts.chart('container', {
                        chart: {
                            type: 'pie'
                        },
                        title: {
                            text: 'Persentase Nilai Pembelian - Semua Metode Pengiriman'
                        },
                        subtitle: {
                            text: 'Klik di potongan kue untuk melihat detil nilai pembelian berdasarkan tahun'
                        },
                    
                        accessibility: {
                            announceNewData: {
                                enabled: true
                            },
                            point: {
                                valueSuffix: '%'
                            }
                        },
                    
                        plotOptions: {
                            series: {
                                dataLabels: {
                                    enabled: true,
                                    format: '{point.name}: {point.y:.1f}%'
                                }
                            }
                        },
                    
                        tooltip: {
                            headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
                        },
                    
                        series: [
                            {
                                name: "Pendapatan By Kategori",
                                colorByPoint: true,
                                data: 
                                    <?php 
                                        //TEKNIK GAK JELAS :D
                    
                                        $datanya =  $json_all_kat; 
                                        $data1 = str_replace('["','{"',$datanya) ;   
                                        $data2 = str_replace('"]','"}',$data1) ;  
                                        $data3 = str_replace('[[','[',$data2);
                                        $data4 = str_replace(']]',']',$data3);
                                        $data5 = str_replace(':','" : "',$data4);
                                        $data6 = str_replace('"name"','name',$data5);
                                        $data7 = str_replace('"drilldown"','drilldown',$data6);
                                        $data8 = str_replace('"y"','y',$data7);
                                        $data9 = str_replace('",',',',$data8);
                                        $data10 = str_replace(',y','",y',$data9);
                                        $data11 = str_replace(',y : "',',y : ',$data10);
                                        echo $data11;
                                    ?>
                                
                            }
                        ],
                        drilldown: {
                            series: [
                                
                                    <?php 
                                        //TEKNIK CLEAN
                                        echo $string_data;
                    
                                    ?>
                    
                                    
                                
                            ]
                        }
                    });

// Data untuk bar chart
const categories = <?php echo $json_categories; ?>;
    const data = <?php echo $json_data; ?>;

    // Creating the bar chart with Highcharts
    Highcharts.chart('bar-container', {
        chart: {
            type: 'column' // Vertical column chart
        },
        title: {
            text: 'Quantity by Vendor'
        },
        xAxis: {
            categories: categories, // Product categories on x-axis (horizontal)
            title: {
                text: 'Vendors' // Set title for x-axis (Products)
            },
            labels: {
                rotation: -45, // Rotate labels to fit longer text
                style: {
                    fontSize: '12px' // Set font size for readability
                }
            }
        },
        yAxis: {
            title: {
                text: 'Quantity' // Set title for y-axis (Total Sales)
            },
            labels: {
                format: '{value:,.0f}' // Format the labels on y-axis with commas
            },
            min: 0, // Minimum value for y-axis
            max: 300, // Maximum value for y-axis (set to a higher value, like 100 million)
            tickInterval: 300, // Interval between ticks (in this case, every 10 million)
        },
        plotOptions: {
            column: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        series: [{
            name: 'Purchases',
            data: data // Sales data is plotted on the y-axis
        }]
    });


    const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($shipMethods); ?>,
                datasets: <?php echo json_encode($datasets); ?>
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Total Purchase per Year by Ship Method'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
                    </script>
                  

                   </div>
               
                </div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->

            
            
            </div>
            <!--/.direct-chat -->

            
          </section>
          <!-- /.Left col -->
          <!-- right col (We are only adding the ID to make the widgets sortable)-->
          
          
          <!-- right col -->
        </div>
                    
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
 

  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.2.0
    </div>
  </footer>
  
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>

</body>
</html>
