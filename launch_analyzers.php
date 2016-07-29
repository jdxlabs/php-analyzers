<?php
error_reporting(E_ALL & ~E_NOTICE);

if ($argv[1] == 'help') {
    exit('php launch_analyzers.php <project_dir> [<project_name>]' . "\n");
}

$basedir      = dirname(__FILE__);
$project_dir  = (string) $argv[1];
$project_name = (string) $argv[2] ? : 'project';
if (!$project_dir || !file_exists($project_dir)) {
    exit('Err : Missing directory for analyzers.' . "\n");
}

function exec_cmd($cmd)
{
    $output = [];
    echo 'CMD : ' . $cmd . "\n";
    exec($cmd, $output);
    echo "\n\n";

    return $output;
}

function make_report_dir($basedir, $project_name)
{
    $report_dir = $basedir . '/data/' . $project_name . '_' . date('Ymd_His');
    echo 'report_dir : ' . $report_dir . " <br>\n";

    if (file_exists($project_dir)) {
        exec('rm -R ' . $project_dir);
    }
    exec('mkdir ' . $report_dir);

    return $report_dir;
}

$report_dir = make_report_dir($basedir, $project_name);
$phpmd      = 'vendor/phpmd/phpmd/src/bin/phpmd';
$phpmetrics = 'php vendor/phpmetrics/phpmetrics/bin/phpmetrics';
$phpcs      = 'php phpcs.phar';
$phpcpd     = 'vendor/sebastian/phpcpd/composer/bin/phpcpd';
$phpdoc     = 'vendor/phpdocumentor/phpdocumentor/bin/phpdoc';

echo '********************************************************************************************************************' . "\n";
echo date('Y-m-d H:i:s') . ' : Launch Analyzers : ' . "\n";
echo 'project_dir : ' . $project_dir . "\n";
echo 'project_name : ' . $project_name . "\n";
echo 'report_dir : ' . $report_dir . "\n";

exec_cmd("$phpmd '$project_dir' text codesize,design,unusedcode --reportfile '$report_dir/phpmd.txt'");
exec_cmd("$phpmetrics --report-html='$report_dir/phpmetrics.html' --level=50 -v '$project_dir'");
exec_cmd("$phpcs -vp --extensions=php --error-severity=5 --warning-severity=8 --standard=PSR2 '$project_dir' >> '$report_dir/phpcs.txt'");
exec_cmd("$phpcpd -v --progress '$project_dir' >> '$report_dir/php_cpd.txt'");
exec_cmd("$phpdoc run -d '$project_dir' -t '$report_dir/phpdoc'");

echo "\n";
echo '' . date('Y-m-d H:i:s') . ' : Done. ' . "\n";
echo '********************************************************************************************************************' . "\n";

