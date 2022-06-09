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

    //初始化数据库
    public static function JoinEs($index,$type,$doc){
        $params = [
            "index"=>"$index",
            "body"=>[
                "mappings"=>[
                    "$type"=>[
                        "properties"=>[
                            "$doc"=>[
                                "type"=>"text",
                                "analyzer"=>"ik_max_word"
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return self::$client->indices()->create($params);
    }

    /**
     * 判断索引是否存在
     * @param string $index_name
     * @return bool|mixed|string
     */
    public function ExistsIndex($index_name)
    {
        $params = [
            'index' => $index_name
        ];
        return self::$client->indices()->exists($params);
    }

    /**
     * 添加索引
     * @param $index
     * @param $type
     * @param $obj
     * @return array|callable
     */
    public static function SaveEs($index,$type,$obj){
        $params = [
            "index"=>$index,
            "type"=>$type,
            "id"=>$obj->id,
            "body"=>[
                "goods_name"=>$obj->goods_name,
                "image"=>$obj->image,
                "price"=>$obj->price
            ]
        ];
        return self::$client->index($params);
    }
    /**
     * 删除索引
     * @param string $index_name
     * @return array
     */
    public function DeleteIndex($index_name) {
        $params = ['index' => $index_name];
        return self::$client->indices()->delete($params);
    }

    /**
     * 判断文档存在
     * @param int $id
     * @param string $index_name
     * @param string $type_name
     * @return array|bool
     */
    public function ExistsDoc($id,$index,$type) {
        $params = [
            'index' => $index,
            'type' => $type,
            'id' => $id
        ];
        return self::$client->exists($params);
    }
    /**
     * 更新文档
     * @param int $id
     * @param string $index_name
     * @param string $type_name
     * @param array $body ['doc' => ['title' => '苹果手机iPhoneX']]
     * @return array
     */
    public function update_doc($id,$index_name,$type_name,$body=[]) {
        // 可以灵活添加新字段,最好不要乱添加
        $params = [
            'index' => $index_name,
            'type' => $type_name,
            'id' => $id,
            'body' => $body
        ];
        return self::$client->update($params);
    }

    /**
     * 搜索字段
     * @param $index
     * @param $type
     * @param $search
     * @return array
     */
    public  static function SearchEs($index,$type,$search){
        $params = [
            "index"=>$index,
            "type"=>$type,
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
                        //搜索的字段
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