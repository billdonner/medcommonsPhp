<?
/*
 * Common functions for aiding in OAuth signing / verification
 */
error_reporting(E_ALL ^ E_NOTICE); // OAuth library has warnings
require_once("OAuth.php");
require_once("utils.inc.php");
require_once("db.inc.php");


/**
 * A store based on looking up tokens in the authentication_token table.
 *
 * We need to store 3 different tokens in a full OAuth sequence,
 * so we do that using the authentication_token table, but we 
 * store the relationships between the consumer token and the 
 * access and request tokens using the at_parent_at_id.
 */
class AuthTokenOAuthDataStore extends OAuthDataStore {/*{{{*/
    private $consumer;
    private $request_token;
    private $access_token;
    private $nonce;

    function __construct() {/*{{{*/
        $this->consumer = new OAuthConsumer("key", "secret", NULL);
        $this->request_token = new OAuthToken("requestkey", "requestsecret", 1);
        $this->access_token = new OAuthToken("accesskey", "accesssecret", 1);
        $this->nonce = "nonce";
    }/*}}}*/

    function lookup_consumer($consumer_key) {/*{{{*/
        dbg("lookup consumer key $consumer_key");
        if ($consumer_key == $this->consumer->key)
          return $this->consumer;

        $db = DB::get();
        $consumers = $db->query("select * from authentication_token where at_token = ? and at_secret is not null",
                                 array($consumer_key));

        dbg("found ".count($consumers)." consumers");
        if(count($consumers) > 0) {
          dbg("found consumer {$consumers[0]->at_token} {$consumers[0]->at_secret}");
          return new OAuthConsumer($consumers[0]->at_token, $consumers[0]->at_secret);
        }

        return NULL;
    }/*}}}*/

    function lookup_token($consumer, $token_type, $token) {/*{{{*/
        dbg("lookup token consumer: {$consumer->key} token: $token");
        $token_attrib = $token_type . "_token";
        if ($consumer->key == $this->consumer->key
            && $token == $this->$token_attrib->key) {
            return $this->$token_attrib;
        }

        $db = DB::get();
        $tokens = $db->query("select * 
                              from authentication_token consumer, authentication_token t
                              where t.at_parent_at_id = consumer.at_id
                              and consumer.at_token = ?
                              and t.at_token = ?",
                               array($consumer->key, $token));

        dbg("found ".count($tokens)." tokens matching token $token");
        if(count($tokens) > 0) {
          dbg("found token ".$tokens[0]->at_token." with secret ".$tokens[0]->at_secret);
          return new OAuthToken($tokens[0]->at_token, $tokens[0]->at_secret);
        }

        return NULL;
    }/*}}}*/

    function lookup_nonce($consumer, $token, $nonce, $timestamp) {/*{{{*/
      //return $this->nonce;
      // For now we don't support nonces.  Just pretend we never saw it before.
      return NULL;
    }/*}}}*/

    function new_request_token($consumer) {/*{{{*/
        $token = new OAuthToken($this->generate_authentication_token(), $this->generate_authentication_token());

        $db = DB::get();

        $at = $db->query("select at_id, ea_name from authentication_token, external_application
                          where at_token = ? and ea_key = at_token",
                          array($consumer->key));

        if(count($at) == 0) 
          throw new Exception("Unknown consumer");

        $at = $at[0];

        dbg("issuing token for application {$at->ea_name} id={$at->at_id}");

        // Create an external share for the new request token
        // Point the external share at our consumer
        $esId = $db->execute("insert into external_share (es_id, es_identity, es_identity_type) 
                      values (NULL, ?, 'Application')", array($at->ea_name)); 

        $db->execute("insert into authentication_token (at_id, at_token, at_secret, at_es_id, at_parent_at_id)
                      values (NULL, ?, ?, ?, ?)",
                      array($token->key, $token->secret, $esId, $at->at_id));

        return $token;

    }/*}}}*/

    function new_access_token($token, $consumer) {/*{{{*/
        $db = DB::get();

        $rt = $db->query("select * from authentication_token where at_token = ?",array($token->key));

        $cn = $db->query("select * from authentication_token where at_token = ?",array($consumer->key));

        if(count($cn)==0)
          throw new Exception("invalid consumer token {$consumer->key}");

        if(count($rt) > 0) {
          dbg("esId for request token is {$rt[0]->at_es_id}");

          $access_token = new OAuthToken($this->generate_authentication_token(), $this->generate_authentication_token());

          // Find the es_id from the request token and assign it to the access token
          $db->execute("insert into authentication_token (at_id, at_token, at_secret, at_es_id, at_parent_at_id)
                        values (NULL, ?, ?, ?, ?)",
                        array($access_token->key, $access_token->secret, $rt[0]->at_es_id, $cn[0]->at_id));

          // Now delete the request token so it can't be used again
          $db->execute("delete from authentication_token where at_id = ?", array($rt[0]->at_id));

          return $access_token;
        }
        throw new Exception("Invalid request token ".$token->key);
    }/*}}}*/

    function generate_authentication_token() {
        // Generate a random id
        return sha1(strval(time()).strval(rand()));
    }
}/*}}}*/


