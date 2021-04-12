<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');

if (isset($_REQUEST["sname"])) {
    $sname = (string)($_REQUEST["sname"]);
    if (stripos($sname, "/") || stripos($sname, " ")) exit("wrong name");
    $filepath = file_path($sname);
    $output = shell_exec("sh $filepath");
    $_SESSION['output'] = $output;
    $baseurl = $_SERVER["SERVER_NAME"];
    header("Location: $baseurl");
    exit("Done: $sname launched");
}
?>

<?php
function file_path($filename)
{
    return "../" . "$filename";
}
?>

<?php
$output = $_SESSION['output'];
$fileslist = array_filter(scandir(file_path("")), function ($filename) {
    if (stripos($filename, "update") && stripos($filename, ".sh")) return true;
    return false;
});
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">

<div class='container my-5'>
    <div class='jumbotron '>
        <?php if ($output) : ?>
            <div class="p-2">
                <p>Ouput: <samp><?= $output; ?></samp></p>
            </div>
            <hr>
        <?php endif; ?>

        <?php if (!count($fileslist)) : ?>
            <div class="p-2 text-center">
                <p>No update-script files</p>
            </div>
        <?php endif; ?>

        <?php foreach ($fileslist as $key => $value) : ?>
            <div class="py-2 px-1 px-sm-3 px-md-4 px-lg-5">
                <a href='?sname=<?= $value; ?>' class='btn btn-info btn-block'><span class="badge badge-info"><?= $key; ?></span> Run: <?= $value; ?></a>
            </div>
        <?php endforeach; ?>
    </div>
</div>