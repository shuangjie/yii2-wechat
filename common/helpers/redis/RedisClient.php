<?php

namespace common\helpers\redis;

use yii;
use yii\redis\Connection;

/**
 * @method blpop($key,$timeout) // key [key ...] timeout remove and get the first element in a list, or block until one is available
 * @method brpop($key,$timeout) // key [key ...] timeout remove and get the last element in a list, or block until one is available
 * @method brpoplpush() // source destination timeout pop a value from a list, push it to another list and return it; or block until one is available
 * @method client_kill() // ip:port kill the connection of a client
 * @method client_list() // get the list of client connections
 * @method client_getname() // get the current connection name
 * @method client_setname() // connection-name set the current connection name
 * @method config_get() // parameter get the value of a configuration parameter
 * @method config_set() // parameter value set a configuration parameter to the given value
 * @method config_resetstat() // reset the stats returned by info
 * @method dbsize() // return the number of keys in the selected database
 * @method debug_object() // key get debugging information about a key
 * @method debug_segfault() // make the server crash
 * @method decr() // key decrement the integer value of a key by one
 * @method decrby($key,$decrement) // key decrement decrement the integer value of a key by the given number
 * @method del($key) // key [key ...] delete a key
 * @method discard() // discard all commands issued after multi
 * @method dump() // key return a serialized version of the value stored at the specified key.
 * @method echo() // message echo the given string
 * @method eval() // script numkeys key [key ...] arg [arg ...] execute a lua script server side
 * @method evalsha() // sha1 numkeys key [key ...] arg [arg ...] execute a lua script server side
 * @method exec() // execute all commands issued after multi
 * @method exists($key) // key determine if a key exists
 * @method expire($key,$seconds) // key seconds set a key's time to live in seconds
 * @method expireat() // key timestamp set the expiration for a key as a unix timestamp
 * @method flushall() // remove all keys from all databases
 * @method flushdb() // remove all keys from the current database
 * @method get($key) // key get the value of a key
 * @method getbit() // key offset returns the bit value at offset in the string value stored at key
 * @method getrange() // key start end get a substring of the string stored at a key
 * @method getset() // key value set the string value of a key and return its old value
 * @method hdel($key,$field) // key field [field ...] delete one or more hash fields
 * @method hexists($key,$field) // key field determine if a hash field exists
 * @method hget($key,$field) // key field get the value of a hash field
 * @method hgetall($key) // key get all the fields and values in a hash
 * @method hincrby($key,$field,$increment) // key field increment increment the integer value of a hash field by the given number
 * @method hincrbyfloat($key,$field,$floatIncrement) // key field increment increment the float value of a hash field by the given amount
 * @method hkeys($key) // key get all the fields in a hash
 * @method hlen($key) // key get the number of fields in a hash
 * @method hmget($key,$field) // key field [field ...] get the values of all the given hash fields
 * @method hmset($key,$field,$value) // key field value [field value ...] set multiple hash fields to multiple values
 * @method hset($key,$field,$value) // key field value set the string value of a hash field
 * @method hsetnx($key,$field,$value) // key field value set the value of a hash field, only if the field does not exist
 * @method hvals($key) // key get all the values in a hash
 * @method incr($key) // key increment the integer value of a key by one
 * @method incrby($key,$increment) // key increment increment the integer value of a key by the given amount
 * @method incrbyfloat() // key increment increment the float value of a key by the given amount
 * @method info() // [section] get information and statistics about the server
 * @method keys($pattern) // pattern find all keys matching the given pattern
 * @method lastsave() // get the unix time stamp of the last successful save to disk
 * @method lindex() // key index get an element from a list by its index
 * @method linsert() // key before|after pivot value insert an element before or after another element in a list
 * @method llen($key) // key get the length of a list
 * @method lpop($key) // key remove and get the first element in a list
 * @method lpush($key,$value) @param $value []|string // key value [value ...] prepend one or multiple values to a list
 * @method lpushx() // key value prepend a value to a list, only if the list exists
 * @method lrange($key,$start,$stop) // key start stop get a range of elements from a list
 * @method lrem($key,$count,$value) // key count value remove elements from a list
 * @method lset() // key index value set the value of an element in a list by its index
 * @method ltrim() // key start stop trim a list to the specified range
 * @method mget($keys) // key [key ...] get the values of all the given keys
 * @method migrate() // host port key destination-db timeout atomically transfer a key from a redis instance to another one.
 * @method monitor() // listen for all requests received by the server in real time
 * @method move() // key db move a key to another database
 * @method mset() // key value [key value ...] set multiple keys to multiple values
 * @method msetnx() // key value [key value ...] set multiple keys to multiple values, only if none of the keys exist
 * @method multi() // mark the start of a transaction block
 * @method object() // subcommand [arguments [arguments ...]] inspect the internals of redis objects
 * @method persist() // key remove the expiration from a key
 * @method pexpire() // key milliseconds set a key's time to live in milliseconds
 * @method pexpireat() // key milliseconds-timestamp set the expiration for a key as a unix timestamp specified in milliseconds
 * @method ping() // ping the server
 * @method psetex() // key milliseconds value set the value and expiration in milliseconds of a key
 * @method psubscribe() // pattern [pattern ...] listen for messages published to channels matching the given patterns
 * @method pttl() // key get the time to live for a key in milliseconds
 * @method publish() // channel message post a message to a channel
 * @method punsubscribe() // [pattern [pattern ...]] stop listening for messages posted to channels matching the given patterns
 * @method quit() // close the connection
 * @method randomkey() // return a random key from the keyspace
 * @method rename() // key newkey rename a key
 * @method renamenx() // key newkey rename a key, only if the new key does not exist
 * @method restore() // key ttl serialized-value create a key using the provided serialized value, previously obtained using dump.
 * @method rpop($key) // key remove and get the last element in a list
 * @method rpoplpush() // source destination remove the last element in a list, append it to another list and return it
 * @method rpush(string $key,$data) // key value [value ...] append one or multiple values to a list
 * @method rpushx() // key value append a value to a list, only if the list exists
 * @method sadd($key,$data) // key member [member ...] add one or more members to a set
 * @method save() // synchronously save the dataset to disk
 * @method scard() // key get the number of members in a set
 * @method script_exists() // script [script ...] check existence of scripts in the script cache.
 * @method script_flush() // remove all the scripts from the script cache.
 * @method script_kill() // kill the script currently in execution.
 * @method script_load() // script load the specified lua script into the script cache.
 * @method sdiff() // key [key ...] subtract multiple sets
 * @method sdiffstore() // destination key [key ...] subtract multiple sets and store the resulting set in a key
 * @method select() // index change the selected database for the current connection
 * @method set($key,$value) // key value set the string value of a key
 * @method setbit() // key offset value sets or clears the bit at offset in the string value stored at key
 * @method setex($key,$seconds,$value) // key seconds value set the value and expiration of a key
 * @method setnx() // key value set the value of a key, only if the key does not exist
 * @method setrange() // key offset value overwrite part of a string at key starting at the specified offset
 * @method shutdown() // [nosave] [save] synchronously save the dataset to disk and then shut down the server
 * @method sinter() // key [key ...] intersect multiple sets
 * @method sinterstore() // destination key [key ...] intersect multiple sets and store the resulting set in a key
 * @method sismember() // key member determine if a given value is a member of a set
 * @method slaveof() // host port make the server a slave of another instance, or promote it as master
 * @method slowlog() // subcommand [argument] manages the redis slow queries log
 * @method smembers() // key get all the members in a set
 * @method smove() // source destination member move a member from one set to another
 * @method sort($key) // key [by pattern] [limit offset count] [get pattern [get pattern ...]] [asc|desc] [alpha] [store destination] sort the elements in a list, set or sorted set
 * @method spop($key) // key remove and return a random member from a set
 * @method srandmember() // key [count] get one or multiple random members from a set
 * @method srem($key,$data) // key member [member ...] remove one or more members from a set
 * @method strlen() // key get the length of the value stored in a key
 * @method subscribe() // channel [channel ...] listen for messages published to the given channels
 * @method sunion() // key [key ...] add multiple sets
 * @method sunionstore() // destination key [key ...] add multiple sets and store the resulting set in a key
 * @method sync() // internal command used for replication
 * @method time() // return the current server time
 * @method ttl($key) // key get the time to live for a key
 * @method type() // key determine the type stored at key
 * @method unsubscribe() // [channel [channel ...]] stop listening for messages posted to the given channels
 * @method unwatch() // forget about all watched keys
 * @method watch() // key [key ...] watch the given keys to determine execution of the multi/exec block
 * @method zadd($key,$score,$member) // key score member [score member ...] add one or more members to a sorted set, or update its score if it already exists
 * @method zcard($key) // key get the number of members in a sorted set
 * @method zcount() // key min max count the members in a sorted set with scores within the given values
 * @method zincrby() // key increment member increment the score of a member in a sorted set
 * @method zinterstore() // destination numkeys key [key ...] [weights weight [weight ...]] [aggregate sum|min|max] intersect multiple sorted sets and store the resulting sorted set in a new key
 * @method zrange($key,$start,$stop,$w ="") // key start stop [withscores] return a range of members in a sorted set, by index
 * @method zrangebyscore($key,$min,$max,$w="") // key min max [withscores] [limit offset count] return a range of members in a sorted set, by score
 * @method zrank($key,$member) // key member determine the index of a member in a sorted set
 * @method zrem($key,$member) // key member [member ...] remove one or more members from a sorted set
 * @method zremrangebyrank($key,$start,$stop) // key start stop remove all members in a sorted set within the given indexes
 * @method zremrangebyscore($key,$min,$max) // key min max remove all members in a sorted set within the given scores
 * @method zrevrange($key,$start,$stop,$c = "" or "withscores") // key start stop [withscores] return a range of members in a sorted set, by index, with scores ordered from high to low
 * @method zrevrangebyscore() // key max min [withscores] [limit offset count] return a range of members in a sorted set, by score, with scores ordered from high to low
 * @method zrevrank() // key member determine the index of a member in a sorted set, with scores ordered from high to low
 * @method zscore($key,$member) // key member get the score associated with the given member in a sorted set
 * @method zunionstore() // destination numkeys key [key ...] [weights weight [weight ...]] [aggregate sum|min|max] add multiple sorted sets and store the resulting sorted set in a new key
 * class redisclient
 *
 */
class RedisClient extends Connection
{

    private static $_client = [];
    /**
     * @param string $component_id 默认是 redis
     * 通过 Yii::$app->get($component_id) 获取相应的connection
     * @return self
     */
    public static function getRedisClient($component_id = 'redis'){
        if(!isset(self::$_client[$component_id])){
            self::$_client[$component_id] = Yii::$app->get($component_id);
            self::$redisClient->redisCommands[] = "SCAN";
            self::$redisClient->redisCommands[] = "SSCAN";//set
            self::$redisClient->redisCommands[] = "HSCAN";//hash
            self::$redisClient->redisCommands[] = "ZSCAN";//sort set
        }
        return self::$_client[$component_id];
    }



    /** @var  RedisClient */
    protected static $redisClient;
    /**
     * @return RedisClient
     */
    public static function defaultRedisClient()
    {
        if(self::$redisClient == null)
        {
            self::$redisClient = \Yii::$app->redisMaster;
            self::$redisClient->redisCommands[] = "SCAN";
            self::$redisClient->redisCommands[] = "SSCAN";//set
            self::$redisClient->redisCommands[] = "HSCAN";//hash
            self::$redisClient->redisCommands[] = "ZSCAN";//sort set
        }
        return self::$redisClient;
    }

    protected static $counterRedisClient;

    /**
     * @return RedisClient
     */
    public static function counterRedisClient()
    {
        if(self::$counterRedisClient == null)
        {
            self::$counterRedisClient = \Yii::$app->counterRedis;
            self::$counterRedisClient->redisCommands[] = "SCAN";
            self::$counterRedisClient->redisCommands[] = "SSCAN";//set
            self::$counterRedisClient->redisCommands[] = "HSCAN";//hash
            self::$counterRedisClient->redisCommands[] = "ZSCAN";//sort set
        }
        return self::$counterRedisClient;
    }

}