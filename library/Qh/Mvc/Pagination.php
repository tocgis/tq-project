<?php
namespace Qh\Mvc;

/**
 * 分页
 */
class Pagination
{
    public $limit;
    public $offset;
    public $nextPage;
    public $totalRows;
    public $totalPages;
    public $pageNumber;
    public $pageSize;

    public function __construct($count,$index = 1,$size = 10) {
        /* 获得HTTP参数 */
        $request = \TQ\Dispatcher::getInstance()->getRequest();

        $page_index = ($request->get('start'))?$request->get('start'):$request->get('page_index'); // 第一条数据的起始位置
        $page_size  = ($request->get('length'))?$request->get('length'):$request->get('page_size'); // 每页的条数

        //print_r($page_index);

        $this->pageNumber   = !empty($page_index)?$page_index:1; //当前页
        $this->pageSize     = !empty($page_size)?$page_size:10;   //每页记录数
        if ($page_index < 1) $page_index = 1;
        if ($page_size < 1 ) $page_size = 10;

        $this->totalPages   = ceil($count/$this->pageSize); //最大页数

        //if ($this->pageNumber > $this->totalPages) $this->pageNumber = $this->totalPages;
        if ($this->pageNumber < 1 ) $this->pageNumber = 1;

        $this->offset = ($this->pageNumber -1) * $this->pageSize;
        $this->limit  = $this->pageSize;
        $this->totalRows = $count;
        $page = array(
            'start'=>(int)$page_index,
            'length'=>(int)$page_size,
            'total'=>(int)$count,
        );
        if ($this->totalPages > $page_index) {
            $page['lastPage'] = false;
            $page['has_next']= true;
        }
        elseif ($this->totalPages = $page_index) {
            $page['lastPage'] = true;
            $page['has_next']= false;
        }
        else {
            $page['has_next']= false;
        }
        $this->page = $page;

    }
}
