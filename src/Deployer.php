<?php
    define('DB_HOST', $argv[1]);
    define('DB_USER', $argv[2]);
    define('DB_PASS', $argv[3]);
    define('DB_NAME', $argv[4]);
    define('TABLE_PREFIX', $argv[5]);
    define('S3_ENDPOINT', $argv[6]);
    define('S3_BUCKET', $argv[7]);
    define('S3_ACCESS', $argv[8]);
    define('S3_SECRET', $argv[9]);
    define('DEPLOY_PATH', $argv[10]);
    define('TABLE_LOG', TABLE_PREFIX.'wp2static_log');
    define('TABLE_STATE', TABLE_PREFIX.'wp2static_addon_s3cmd_options');

    try {
        $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
        $stmt = $db->prepare('SELECT value FROM '.TABLE_STATE.' WHERE name="deploying" LIMIT 0,1');
        $stmt->execute();
        $deploying = $stmt->fetchAll(PDO::FETCH_OBJ)[0]->value;        
    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }

    if ($deploying==0) {
        setState('deploying',1);
        logWP2S("Deploying... Please wait");
        do {            
            setState('queue',0);
            $cmd = 's3cmd --host='.S3_ENDPOINT.' --access_key='.S3_ACCESS.' --secret_key='.S3_SECRET.' -s --no-encrypt --host-bucket=buckets.s3.amazonaws.com ';
            $cmd.= '--acl-public --no-preserve --no-mime-magic sync '.DEPLOY_PATH.'/. s3://'.S3_BUCKET;
            exec($cmd,$result);            
            var_dump ($result);
        } while (getQueue()!=0);
        logWP2S('Deploy ended, queue empty');
        setState('deploying',0);        
    } else {
        setState('queue',1);
        logWP2S("There is a deploy already in progress... we'll manage it at the same time ;)");
    }

    function setState($name,$value) {
        global $db;
        $stmt = $db->prepare('UPDATE '.TABLE_STATE.' SET value=:value WHERE name=:name');
        $stmt->bindParam(':value',$value);
        $stmt->bindParam(':name',$name);
        $stmt->execute();
    }

    function getQueue() {
        global $db;
        $stmt = $db->prepare('SELECT value FROM '.TABLE_STATE.' WHERE name="queue" LIMIT 0,1');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ)[0]->value;
    }

    function logWP2S($info) {
        global $db;
        $stmt = $db->prepare('INSERT INTO '.TABLE_LOG.' (log) VALUES (:info)');
        $stmt->bindParam(':info',$info);
        $stmt->execute();
    }

?>