<?php
namespace common\extensions\wechatpay\lib;

use yii\base\Object;
use ArrayIterator;

/**
 * 基础数据类
 * DataCollection 用来
 * 继承 SPL（Standard PHP Library） 标准化库 IteratorAggregate ArrayAccess Countable
 */

class DataCollection extends Object  implements \IteratorAggregate, \ArrayAccess, \Countable
{

    protected $_values = [];

    protected $_signKey = '';

    /**
     * Returns an iterator for traversing the datas in the collection.
     * This method is required by the SPL interface [[\IteratorAggregate]].
     * It will be implicitly called when you use `foreach` to traverse the collection.
     * @return ArrayIterator an iterator for traversing the datas in the collection.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_values);
    }

    /**
     * Returns the number of headers in the collection.
     * This method is required by the SPL `Countable` interface.
     * It will be implicitly called when you use `count($collection)`.
     * @return integer the number of headers in the collection.
     */
    public function count()
    {
        return $this->getCount();
    }

    /**
     * Returns the number of headers in the collection.
     * @return integer the number of headers in the collection.
     */
    public function getCount()
    {
        return count($this->_values);
    }

    /**
     * Returns the named data(s).
     * @param string $name the name of the data to return
     * @param mixed $default the value to return in case the named data does not exist
     * @return mixed the named data(s).
     * */
    public function get($name, $default = null){
        return isset($this->_values[$name]) ? $this->_values[$name] : $default;
    }

    /**
     * Adds a new data.
     * If there is already a data with the same name, it will be replaced.
     * @param string $name the name of the data
     * @param string $value the value of the data
     * @return $this the collection object itself
     */
    public function set($name, $value = '')
    {
        $this->_values[$name] = $value;
        return $this;
    }

    /**
     * Returns whether there is a data with the specified name.
     * @param string $name the data name
     * @return boolean whether the named data exists
     * @see remove()
     */
    public function has($name)
    {
        return isset($this->_values[$name]) && $this->_values[$name] !== '';
    }

    /**
     * Returns whether there is a data with the specified name.
     * It is implicitly called when you use something like `isset($collection[$name])`.
     * @param string $name the data name
     * @return boolean whether the named data exists
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * Removes a data.
     * @param string $name the name of the data to be removed.
     * @return array the value of the removed data. Null is returned if the data does not exist.
     */
    public function remove($name)
    {
        if (isset($this->_values[$name])) {
            $value = $this->_values[$name];
            unset($this->_values[$name]);
            return $value;
        } else {
            return null;
        }
    }

    /**
     * Removes all datas.
     */
    public function removeAll()
    {
        $this->_values = [];
    }

    /**
     * Returns the collection as a PHP array.
     * @return array the array representation of the collection.
     * The array keys are data names, and the array values are the corresponding data values.
     */
    public function toArray()
    {
        return $this->_values;
    }

    /**
     * Populates the data collection from an array.
     * @param array $array the datas to populate from
     */
    public function fromArray(array $array)
    {
        $this->_values = $array;
    }

    /**
     * return the collection as a xml string.
     * @return string|boolean the string representation of the collection.
     */
    public function toXml()
    {
        if(!is_array($this->_values)
            || count($this->_values) <= 0)
        {
            return false;
        }
        $xml = "<xml>";
        foreach ($this->_values as $key => $val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * return the collection as a url query string
     * @return string  the string  representation of the collection.
     */
    public function toUrlParams(){
        $values = $this->_values;
        $buff = "";
        foreach ($values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
//        if(isset($values['sign'])){
//            unset($values['sign']);
//        }
//        $values = array_filter($values, function($v){
//            return !empty($v) && !is_array($v);
//        });
//        return http_build_query($values);
    }


    /**
     * Populates the data collection from xml string.
     * @param string $xml the xml to populate from
     * @return array|boolean
     * */
    public function fromXml($xml){
        if(!$xml){
            return false;
        }
        libxml_disable_entity_loader(true);
        $this->_values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->_values;
    }

    /**
     * Returns the data with the specified name.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `$header = $collection[$name];`.
     * This is equivalent to [[get()]].
     * @param string $name the header name
     * @return string the header value with the specified name, null if the named header does not exist.
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Adds the header to the collection.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `$collection[$name] = $data;`.
     * @param string $name the header name
     * @param string $value the header value to be added
     */
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Removes the named data.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `unset($collection[$name])`.
     * This is equivalent to [[remove()]].
     * @param string $name the data name
     */
    public function offsetUnset($name)
    {
        $this->remove($name);
    }


    /* --------------  Security method    -------------- */

    public function setSignKey($key){
        $this->_signKey = $key;
    }

    /**
     * Add a data named 'sign'
     * @param string $key
     * @return string
     */
    public function setSign($key = ''){
        empty($key) && $key = $this->_signKey;
        $sign = $this->makeSignature($key);
        $this->_values['sign'] = $sign;
        return $sign;
    }

    /**
     * make signature for api request
     * @param string $key  api password to make signature
     * @return string
     */
    public function makeSignature($key)
    {
        //签名步骤一：按字典序排序参数
        ksort($this->_values);
        $string = $this->toUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$key;
//        echo $string;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
}