<?php


function returnprice($typeid=34,$regionid='forge')
{

        $url="http://api.eve-marketdata.com/api/item_prices2.xml?char_name=steveronuken&buysell=a&type_ids=".$typeid;
        $pricexml=file_get_contents($url);
        $xml=new SimpleXMLElement($pricexml);
        $price= (float) $xml->result->rowset->row['price'][0];
        $price=round($price,2);
        if (!(is_numeric($price)))
        {
            $price=0;
        }
        $buyprice= (float) $xml->result->rowset->row['price'][1];
        $buyprice=round($price,2);
        if (!(is_numeric($buyprice)))
        {
            $buyprice=0;
        }

        return array($price,$buyprice);

}

?>
