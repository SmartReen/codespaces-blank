<?php
session_start();
if (isset($_SESSION['sessionid'])) {
    $adminemail = $_SESSION['adminemail'];
    $adminpass = $_SESSION['adminpass'];
    $userid = $_SESSION['usr_id'];
}else{
    echo "<script>alert('No session available. Please login.');</script>";
    echo "<script>window.location.replace('login.php')</script>";
}

 //Form data handler
if (isset($_POST['submit'])) {
    $productname = $_POST['title'];
    $productdescription = $_POST['content'];
    $producttype = $_POST['type'];
    $productprice = $_POST['price'];
    $productstock = $_POST['stock'];
    $productpicture = "newsfile" . date("dmY") . "-" . randomString(5) . ".png";
    $target_dir = "uploads/";
    $target_file = $target_dir . $productpicture;

    $sqliproducts = "INSERT INTO `tbl_products`(`prd_name`, `prd_picture`, `prd_description`, `prd_type`, `prd_price`, `prd_stock`) 
                     VALUES ('$productname', '$productpicture', '$productdescription', '$producttype', '$productprice', '$productstock')";

    try {
        include("dbconnect.php"); // Sambungan ke database
        $conn->query($sqliproducts);

        if (move_uploaded_file($_FILES["newsfile"]["tmp_name"], $target_file)) {
            echo "<script>alert('Success')</script>";
            echo "<script>window.location.replace('products.php')</script>";
        } else {
            echo "<script>alert('File upload failed!')</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Failed: " . $e->getMessage() . "')</script>";
    }
}


//search operation based on search form
if (isset($_GET['btnsearch'])) {
    $search = $_GET['search'];
    $searchby = $_GET['searchby'];

    if ($searchby == "title") {
        $sqlloadproducts = "SELECT * FROM `tbl_products` WHERE `prd_name` LIKE '%$search%'";
    }
    if ($searchby == "content") {
        $sqlloadproducts = "SELECT * FROM `tbl_products` WHERE `prd_description` LIKE '%$search%'";  
    }
}else{
    $sqlloadproducts = "SELECT * FROM `tbl_products`";
}

if (isset($_GET['submit'])) {
    $operation = $_GET['submit'];
    $productid = $_GET['productid'];
    if ($operation == "delete") {
        $sqldeleteproducts = "DELETE FROM `tbl_products` WHERE `prd_id` = '$productid'";
        try{
            include("dbconnect.php"); // database connection
            $conn->query($sqldeleteproducts);
            echo "<script>alert('Success')</script>";
            echo "<script>window.location.replace('products.php')</script>";
        }catch(PDOException $e){
            echo "<script>alert('Failed!!!')</script>";
            echo "<script>window.location.replace('products.php')</script>";
        }
    }
}

//load data
$results_per_page = 10;
if (isset($_GET["pageno"])) {
    $pageno = (int) $_GET["pageno"];
    $page_first_result = ($pageno - 1) * $results_per_page;
} else {
    $pageno = 1;
    $page_first_result = 0;
}
include("dbconnect.php"); // database connection

$stmt = $conn->prepare($sqlloadproducts);
$stmt->execute();
$number_of_rows = $stmt->rowCount();
$number_of_page = ceil($number_of_rows / $results_per_page);
$sqlloadproducts = $sqlloadproducts . " LIMIT $page_first_result, $results_per_page";
$stmt = $conn->prepare($sqlloadproducts);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function randomString($length = 10)
{
    $characters =
        "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $charactersLength = strlen($characters);
    $randomString = "";
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function truncate($string, $length, $dots = "...")
{
    return strlen($string) > $length
        ? substr($string, 0, $length - strlen($dots)) . $dots
        : $string;
}

//form data handler


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Portal</title>
    <link rel="stylesheet" href="w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }

    .w3-button {
        transition: background-color 0.3s, transform 0.3s;
    }

    .w3-button:hover {
        background-color: #5a2d6d;
        transform: scale(1.05);
    }

    .w3-table-all th {
        background-color: #6c757d;
        color: white;
    }

    .w3-table-all tr:hover {
        background-color: #e9ecef;
    }

    .modal-content {
        animation: slideIn 0.5s;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    footer {
        background-color: rgb(112, 57, 102);
        color: white;
        text-align: center;
        padding: 16px;

    }
    </style>

</head>

<body>

    <!-- Sidebar -->
    <nav class="w3-sidebar w3-bar-block w3-collapse w3-top w3-card"
        style="z-index:3; width:250px; background-color:rgb(112, 57, 102);" id="mySidebar"
        aria-label="Sidebar Navigation">
        <div class="w3-container w3-display-container w3-padding-16">
            <button onclick="close_menu()" class="fa fa-remove w3-hide-large w3-button w3-display-topright"
                aria-label="Close menu"></button>

        </div>
        <div class="w3-padding-64 w3-large w3-text-grey" style="font-weight:bold;">
            <a href="mainpage.php" class="w3-bar-item w3-button" role="link">
                <i class="fa fa-newspaper-o" aria-hidden="true"></i> News
            </a>
            <a href="memberpage.php" class="w3-bar-item w3-button" role="link">
                <i class="fa fa-users" aria-hidden="true"></i> Members
            </a>
            <a href="eventpage.php" class="w3-bar-item w3-button" role="link">
                <i class="fa fa-calendar" aria-hidden="true"></i> Events
            </a>
            <a href="products.php" class="w3-bar-item w3-button" role="link">
                <i class="fa fa-box" aria-hidden="true"></i> Products
            </a>
            <a href="profilepage.html?adminid=<?php echo htmlspecialchars($adminid); ?>" class="w3-bar-item w 3-button"
                role="link">
                <i class="fa fa-user" aria-hidden="true"></i> Profile
            </a>
            <a href="logout.php" class="w3-bar-item w3-button" role="link">
                <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Top hamburger menu -->
    <header class="w3-bar w3-top w3-hide-large w3-hide-small w3-xlarge">
        <div class="w3-bar-item w3-padding-24 w3-wide"></div>
        <a href="javascript:void(0)" class="w3-bar-item w3-button w3-padding-24 w3-right" onclick="open_menu()"><i
                class="fa fa-bars"></i></a>
        <div class="w3-hide-large w3-padding-24 w3-wide w3-right">
            <h6>Product</h6>
        </div>
    </header>
    <!-- Top hamburger menu -->

    <!-- Content -->
    <div class="w3-main" style="margin-left:250px; background-color: rgb(255, 243, 224); padding: 20px;">
        <header class="w3-center"
            style="background-color: rgb(112, 57, 102); padding: 32px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <h1 style="color: #ffffff; font-size: 2.5em;">Product</h1>
            <p style="color: #ffffff; font-size: 1.2em;">Your One Stop Event Manager</p>
        </header>

        <div class="w3-bar-item" style="margin-top: 20px;">
            <button class="w3-button w3-round w3-margin-right w3-hover-shadow"
                style="background-color: rgb(112, 57, 102); color: #ffffff;"
                onclick="document.getElementById('idabout').style.display='block'">
                About
            </button>
            <button class="w3-button w3-round w3-hover-shadow"
                style="background-color: rgb(112, 57, 102); color: #ffffff;"
                onclick="document.getElementById('id01').style.display='block'">
                New Product
            </button>
        </div>

        <div style="height:80px"></div>

        <!-- Search new form -->
        <div class="w3-container"
            style="margin-top: 20px; background-color: rgb(138, 52, 102); border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <form action="products.php">
                <header class="w3-center w3-padding">
                    <div class="w3-row w3-center">
                        <div class="w3-third w3-padding">
                            <input class="w3-input w3-round w3-border" type="text" name="search" placeholder="Search"
                                aria-label="Search">
                        </div>
                        <div class="w3-third w3-padding">
                            <select class="w3-input w3-round w3-border" name="searchby" aria-label="Search by">
                                <option value="title">Title</option>
                                <option value="content">Content</option>
                            </select>
                        </div>
                        <div class="w3-third w3-padding">
                            <button class="w3-button w3-round w3-teal w3-hover-shadow" type="submit" name="btnsearch"
                                aria-label="Search">
                                <i class="fa fa-search" aria-hidden="true"></i> Search
                            </button>
                        </div>
                    </div>
                </header>
            </form>
        </div>
        <!-- search form ended -->

        <!-- table section  -->
        <div class="w3-container" style="height: 1200px;margin:auto;overflow-y: auto">
            <?php
                if($number_of_rows > 0){
                     //Create table and table headers 
                    echo "<table class='w3-table-all'>";
                    echo "<tr><th>No</th><th>Title</th><th>Content</th><th>Date</th><th>Action</th></tr>";
                    $i =1;
                    $i = $page_first_result + 1;

                    foreach ($rows as $product) { //array traversal
                        $productid = $product['prd_id'];
                        $productname = $product['prd_name'];
                        $productdescription = truncate($product['prd_description'], 250);
                        $productpicture = $product['prd_picture'];
                        $productdate = date_format(date_create($product['prd_time']), "d-m-Y h:i a");
                        
                        //Generate dynamic table row 
                        echo "<tr>
                                <td>$i</td>
                                <td>$productname</td>
                                <td>$productdescription</td>
                                <td>$productdate</td>
                                <td>
                                <a href='editproduct.php?productid=$productid' class='w3-button w3-round w3-green'>&nbsp;Edit&nbsp;&nbsp;</a>
                                <div style='margin-bottom:5px'></div>
                                <a href='products.php?submit=delete&productid=$productid' class='w3-button w3-round w3-red' onclick=\"return confirm ('Delete this product no $i?');\">Delete</a>
                                <div style='margin-bottom:5px'></div>
                                <a href='javascript:void(0)' onclick=\"document.getElementById('id$i').style.display='block'\" class='w3-button w3-round w3-yellow'>&nbsp;View&nbsp;</a>
                                </td>
                            </tr>";
                        
                        //Dynamic modal window    
                        echo " <div id='id$i' class='w3-modal'>
                                <div class='w3-modal-content w3-card-4'>
                                    <header class='w3-container w3-teal'>
                                        <span onclick='document.getElementById(\"id$i\").style.display=\"none\"'
                                        class='w3-button w3-display-topright fa fa-close'></span>
                                        <h3>$productname</h3>
                                    </header>
                                   
            <!-- Integrated Product Image Section -->
            <div class='w3-container w3-center w3-padding-large'>
                <?php
                \$imagePath = \"uploads/\$productpicture\";
                if (file_exists(\$imagePath)) {
                    echo \"<img src='\$imagePath' style='max-width:350px' alt='Product Image'>\";
                } else {
                    echo \"<img src='placeholder.png' style='max-width:350px' alt='Image Not Available'>\";
                }
                ?>
        </div>
        <!-- End of Image Section -->
        <div class='w3-container'>
            Product Date: $productdate
        </div>
        <div class='w3-container w3-padding'>
            <p>$productdescription</p>
        </div>
        <footer class='w3-container w3-teal w3-center'>Putry Event</footer>
    </div>
    </div>";
    $i++;
    }
    echo "</table>";
    } else {
    echo "<h2>No product list found</h2>";
    }
    ?>

    <?php
                echo "<div class='w3-container w3-padding w3-row w3-center'>";
                for ($page = 1; $page <= $number_of_page; $page++) {
                    echo '<a href="products.php?pageno=' . $page . '" style="text-decoration: none">&nbsp&nbsp' . $page . " </a>";
                }
                echo " ( " . $pageno . " )";
                echo "</div>";
            ?>
    </div>
    <!-- Content -->

    <div id="id01" class="w3-modal">
        <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px;">
            <header class="w3-container w3-purple">
                <span onclick="document.getElementById('id01').style.display='none'"
                    class="w3-button w3-display-topright">&times;</span>
                <h2>New Product</h2>
            </header>
            <div class="w3-container" style="background-color: rgb(255, 243, 224);">
                <form action="products.php" method="POST" enctype="multipart/form-data" class="w3-margin">
                    <label class="w3-text-purple">Product Name</label>
                    <input class="w3-input w3-border w3-round w3-margin-bottom" type="text" name="title" required>

                    <label class="w3-text-purple">Product Description</label>
                    <textarea class="w3-input w3-border w3-round w3-margin-bottom" name="content" required
                        rows="4"></textarea>

                    <label class="w3-text-purple">Product Type</label>
                    <input class="w3-input w3-border w3-round w3-margin-bottom" type="text" name="type" required>

                    <label class="w3-text-purple">Product Price</label>
                    <input class="w3-input w3-border w3-round w3-margin-bottom" type="text" name="price" required>

                    <label class="w3-text-purple">Product Stock</label>
                    <input class="w3-input w3-border w3-round w3-margin-bottom" type="text" name="stock" required>

                    <label class="w3-text-purple">Upload Product Image (PNG only)</label>
                    <input class="w3-input w3-border w3-round w3-margin-bottom" type="file" name="newsfile"
                        accept=".png" required>

                    <button class="w3-button w3-purple w3-round w3-block" type="submit" name="submit">
                        Insert New Product
                    </button>
                </form>
            </div>
            <footer class="w3-container w3-purple w3-center">
                <p>Putry Event</p>
            </footer>
        </div>
    </div>


    <div id="idabout" class="w3-modal">
        <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px;">
            <header class="w3-container w3-purple">
                <span onclick="document.getElementById('idabout').style.display='none'"
                    class="w3-button w3-display-topright">&times;</span>
                <h3>About App</h3>
            </header>
            <div class="w3-container w3-padding" style="background-color: rgb(255, 243, 224);">
                <p class="w3-justify">
                    This application is developed for Putry Event Sdn Bhd. At Putry Event Sdn Bhd, we specialize in
                    creating unforgettable experiences.
                    Established with a passion for delivering excellence, we are a full-service event management company
                    that brings your visions to life.
                    From corporate gatherings to personal celebrations, we provide customized solutions tailored to meet
                    the unique needs of our clients.
                    Our services include event planning, design, coordination, and execution, ensuring every detail is
                    handled with care and precision.
                    With a dedicated team of professionals and a commitment to quality, Putry Event Sdn Bhd is here to
                    turn your ideas into remarkable events.
                    Your satisfaction is our priority, and we strive to make every event a moment to remember. Let us
                    make your next event extraordinary!
                </p>
            </div>
            <footer class="w3-container w3-purple w3-center">
                <p>Putry Event</p>
            </footer>
        </div>
    </div>

    <!-- modal window -->

    <script>
    function open_menu() {
        document.getElementById("mySidebar").style.display = "block";
    }

    function close_menu() {
        document.getElementById("mySidebar").style.display = "none";
    }
    </script>

    <footer class=" w3-main w3-container" style="background-color:rgb(112, 57, 102);">
        <div class="w3-padding-16">
            <p style="text-align: center; color: white; font-size: 16px; margin: 0;">Copyright &copy; 2023 Putry
                Event Sdn Bhd</p>
        </div>
    </footer>
</body>

</html>
