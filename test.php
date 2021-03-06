<html>
    <head>
    <title>Form.php Örnek Kullanım</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/validation.js"></script>
</head>
<body>
<div align='center'>
<?php



echo "<h1>GELEN DEĞİŞKENLER</h1>";
foreach ($_POST as $key => $val) {
    echo "$key=$val<br/>";
}

echo "<hr/>";

try {
    require_once 'FormCreator/PhpValidation.php';
    //Burada php doğrulamasını test betmeyi amaçlıyorum

    $phpValidation = new PhpValidation();
    $phpValidation->set($_POST['name'], "string","name")->setMinLength(2)->setMaxLength(100)->isEmail();
    $phpValidation->set($_POST['yas'], "int","yas")->setMinValue(0)->setMaxValue(100);
    
    if ($phpValidation->isValid() === false) {
        echo "<h2>Hata Mesajı</h2>";
        var_dump($phpValidation->getMessages());
    }
    
    echo "<hr/>";
    var_dump($phpValidation);
    
} catch (Exception $ex) {
      $ex->getMessage();
}

?>
</div>
</body>
</html>