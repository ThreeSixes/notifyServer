<?php
# Set option to make sure we're opened by notify.
define('openedByNotifyServer', TRUE);
# Open tokens file.
require('tokens.php');

# Response obect.
$resp = [
    "respText" => "",
    "respCode" => 500
];

# Redis stuff.
$redisHost = '127.0.0.1';
$redisPort = 6379;
$redisQName = "notifyQ";

# Read JSON data.
$json = file_get_contents('php://input');
$req = json_decode($json, true);

# See if we got what we want back.
if (is_array($req) == true) {
    # Do we have an auth token?
    if (array_key_exists('authToken', $req) == True) {
        # Try to verify it.
        if (array_key_exists($req['authToken'], $validTokens) == True) {
            # Set status = 200.
            $resp['respCode'] = 200;
            $resp["respText"] = "OK";
            
            # Add source IP address.
            $req['reqSrcIP'] = $_SERVER['REMOTE_ADDR'];
            
            # Add token name and pop token itself.
            $req['authName'] = $validTokens[$req['authToken']];
            unset($req['authToken']);
            
            # Set up Redis
            $redis = new Redis();
            $redis->pconnect($redisHost, $redisPort);
            
            # Check Redis.
            if ($redis->ping() == "+PONG") {
                # Convert reque to JSON
                $jsonifiedReq = json_encode($req);
                
                # Drop message on queue.
                $redis->publish($redisQName, $jsonifiedReq);
                
                # Close redis.
                $redis->close();
            } else {
                # Auth fail.
                $resp['respCode'] = 503;
                $resp["respText"] = "Queue server error.";
            }
        } else {
            # Auth fail.
            $resp['respCode'] = 400;
            $resp["respText"] = "Bad token.";
        }
    } else {
        # No token.
        $resp["respText"] = "No token.";
        $resp['respCode'] = 400;
    }
} else {
    # Malformed request.
    $resp['respCode'] = 400;
    $resp["respText"] = "Bad request.";
}

# Set HTTP response code.
http_response_code($resp['respCode']);

# Set response JSON.
echo json_encode(["message" => $resp["respText"]]);
?>
