<?php


namespace ElasticSearch\src;


use Elasticsearch\ClientBuilder;
//单例模式封装ES
class EsMessage
{
    private function __construct(){
        self::$client =  ClientBuilder::create()->setHosts(["127.0.0.1:9200"])->build();
    }

    private function __clone(){

    }

    /**
     * @return mixed
     */
    public static function getObj()
    {
        if (!self::$client instanceof self){
            self::$obj = new self();
        }
        return self::$obj;
    }

    //添加数据到Es
    public static function SaveEs($obj){
        $params = [
            "index"=>"week3",
            "type"=>"goods",
            "id"=>$obj->id,
            "body"=>[
                "goods_name"=>$obj->goods_name,
                "image"=>$obj->image,
                "price"=>$obj->price
            ]
        ];
        return self::$client->index($params);
    }

    //搜索数据
    public  static function SearchEs($search){
        $params = [
            "index"=>"week3",
            "type"=>"goods",
            "body"=>[
                "query"=>[
                    "match"=>[
                        //搜索的字段
                        "goods_name"=>$search
                    ]
                ],
                "highlight"=>[
                    "pre_tags"=>"<span style='color: red'>",
                    "post_tags"=>"</span>",
                    "fields"=>[
                        "goods_name"=>new \stdClass()
                    ]
                ]
            ]
        ];
        $data = self::$client->search($params);
        $res = [];
        foreach ($data["hits"]["hits"] as &$val){
            $val["_source"]["goods_name"] = $val["highlight"]["goods_name"][0];
            array_push($res,$val["_source"]);
        }
        unset($val);
        return $res;
    }
}