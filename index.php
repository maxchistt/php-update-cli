<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
set_time_limit(120);
?>

<!doctype html>
<html lang="ru">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">

    <title>Updater</title>
</head>

<?php
$steps = 6;
$rebase = isset($_REQUEST["pname"]) && (base64url_decode($_REQUEST["stage"]) < $steps);

if ($rebase) {
    $pname = (string)($_REQUEST["pname"] ? base64url_decode($_REQUEST["pname"]) : "");
    $mode = (string)($_REQUEST["mode"] ? base64url_decode($_REQUEST["mode"]) : "");
    $stage = (int)($_REQUEST["stage"] ? base64url_decode($_REQUEST["stage"]) : 0);
    $oldOutput = (string)($_REQUEST["output"] ? base64url_decode($_REQUEST["output"]) : "");

    if (stripos($pname, ".") !== false || stripos($pname, "/") !== false || stripos($pname, " ") !== false) exit("wrong name");

    $stage++;
    $commands1;
    $commands2;
    $commands3;
    $commands4;
    $commands5;

    if($mode == 'commit'){
        $commands1 = ["cd ../ && cd $pname && git add . && cd ../"];
        $commands2 = ["cd ../ && cd $pname && git commit -m \"remote commit\" && cd ../"];
        $commands3 = [" "];
        $commands4 =  [" "];
        $commands5 =  [" "];
    }else{
        $commands1 = ["cd ../ && cd $pname && git pull --all && cd ../"];
        $commands2 = ["cd ../ && cd $pname && git reset --hard origin && cd ../"];
        $commands3 = ($mode == 'node' || $mode == 'react')
            ? ["cd ../ && cd $pname && npm install && cd ../"]
            : (($mode == 'mern')
                ? ["cd ../ && cd $pname && npm install && cd ../", "cd ../ && cd $pname/server && npm install && cd ../ && cd ../", "cd ../ && cd $pname/client && npm install && cd ../ && cd ../"]
                : [" "]);
        $commands4 = ($mode == 'react')
            ? ["cd ../ && cd $pname && npm run build && cd ../"]
            : (($mode == 'mern')
                ? ["cd ../ && cd $pname && cd client && npm run build && cd ../ && cd ../"]
                : [" "]);
        $commands5 = ($mode == 'node' or $mode == 'react' or $mode == 'mern')
            ? ["cd ../ && pm2 restart ecosystem.config.js","cd ../ && cd $pname && npm run doc && cd ../"]
            : [" "];
    }


    $commands = null;
    $config = '';
    switch ($stage) {
        case 1:
            $config = "pName: " . $pname . " mode: " . $mode . PHP_EOL;
            $commands = $commands1;
            break;
        case 2:
            $commands = $commands2;
            break;
        case 3:
            $commands = $commands3;
            break;
        case 4:
            $commands = $commands4;
            break;
        case 5:
            $commands = $commands5;
            break;
        default:
            $commands = null;
            break;
    }

    $res = "";

    foreach ($commands as $value) {
        $res .= (string)(shell_exec($value));
    }

    $output = $commands ? $oldOutput . PHP_EOL . (string)($config) . (string)($res) : ($oldOutput ? $oldOutput : "no commands...");

    $outputFinEncode = base64url_encode($output);
    $pnameFinEncode = base64url_encode($pname);
    $modeFinEncode = base64url_encode($mode);
    $stageFinEncode = base64url_encode($stage);
}

function base64url_encode($data, $pad = null)
{
    $data = str_replace(array('+', '/'), array('-', '_'), base64_encode($data));
    if (!$pad) {
        $data = rtrim($data, '=');
    }
    return $data;
}

function base64url_decode($data)
{
    return base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
}

?>

<?php
$outputRes = isset($_REQUEST["output"]) ? base64url_decode($_REQUEST["output"]) : false;
$hrefEnable = !$rebase;
$currStage = $rebase ? ((int)(base64url_decode($_REQUEST["stage"])) + 1) : "Final";
$dirlist = array_values(array_filter(scandir("../"), function ($dirname) {
    if (stripos($dirname, ".") !== false) return false;
    if (in_array(".git", scandir("../$dirname"))) return true;
    return false;
}));
?>

<body>
    <style type="text/css">
        samp {
            font-family: source-code-pro, Menlo, Monaco, Consolas, 'Courier New', monospace;
        }

        ::-webkit-scrollbar {
            height: 16px;
            overflow: visible;
            width: 16px;
        }

        ::-webkit-scrollbar-button {
            height: 0;
            width: 0;
        }

        ::-webkit-scrollbar-corner {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background-color: transparent;
            min-height: 28px;
            padding: 100px 0 0;
        }

        body::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
        }
    </style>
    <div class='p-0 m-0' style="overflow:hidden;min-height:90vh;display:flex;flex-direction:column;justify-content:center;">
        <div class='container my-1 ' style="overflow:hidden;">
            <div class='jumbotron py-2 py-sm-4 m-0' style="overflow:auto;min-height:50vh;display:flex;flex-direction:column;justify-content:center;">
                <main class="px-0 mx-0 px-sm-2 mx-sm-3 px-md-4 mx-md-4 px-lg-5 mx-lg-5">
                    <?php if ($outputRes || $rebase) : ?>
                        <?php if ($rebase) : $progress = $currStage / $steps * 100 ?>
                            <div class="progress px-2 mb-2">
                                <div class="rounded progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: <?= $progress; ?>%" aria-valuenow="<?= $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        <?php endif; ?>

                        <div class="px-2 py-0" style="max-height:300px;overflow:auto;">
                            <div class="mx-2 my-0">
                                <pre class="m-0">Stage: <samp><?= $currStage; ?></samp><br>Ouput: <samp><?= ($currStage > 1 or $outputRes) ? $outputRes : "wait..."; ?></samp></pre>
                            </div>
                        </div>

                        <?php if (!$rebase) : ?>
                            <hr>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!$rebase) : ?>
                        <?php if (!count($dirlist)) : ?>

                            <div class="p-2 text-center">
                                <p>No update-script files</p>
                            </div>

                        <?php else : ?>

                            <div class="px-2 pt-0 pb-1 bp-sm-3">
                                <div class='m-2'>Update:</div>
                                <?php foreach ($dirlist as $key => $dir) :
                                    $dirmode = in_array("package.json", scandir("../$dir"))
                                        ? ((in_array("public", scandir("../$dir")) && in_array("src", scandir("../$dir")))
                                            ? "react"
                                            : ((in_array("client", scandir("../$dir")))
                                                ? "mern"
                                                : "node"))
                                        : "other";
                                ?>
                                    <div class='m-2 row'>
                                        <div class="col-9">
                                            <a class='h-100 btn btn-primary btn-block text-left' <?php if ($hrefEnable) :
                                                    $dirEncode = base64url_encode($dir);
                                                    $dirmodeEncode = base64url_encode($dirmode); ?> href='?pname=<?= $dirEncode; ?>&mode=<?= $dirmodeEncode; ?>' <?php endif; ?>>
                                                <span class="badge badge-primary mr-1"><?= ++$key; ?></span><?= $dir; ?>
                                            </a>
                                        </div>
                                        <div class="col-3">
                                            <a class='h-100 btn btn-primary btn-block text-left' <?php if ($hrefEnable) :
                                                    $dirEncode = base64url_encode($dir);
                                                    $dirmodeEncode = base64url_encode("commit"); ?> href='?pname=<?= $dirEncode; ?>&mode=<?= $dirmodeEncode; ?>' <?php endif; ?>>
                                                    Commit
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php endif; ?>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>

</body>

</html>



<?php if ($rebase) : ?>

    <script type="text/javascript">
        setTimeout(function() {
            location = "/?output=<?= $outputFinEncode; ?>&pname=<?= $pnameFinEncode; ?>&mode=<?= $modeFinEncode; ?>&stage=<?= $stageFinEncode; ?>";
        }, 200);
    </script>

<?php endif; ?>