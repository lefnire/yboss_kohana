This project is a clone of http://code.google.com/p/yahoo-boss-php, which was originally intended for CodeIgniter, but with a few tiny tweaks is made compatible with Kohana.

To install, place this in /modules , and add a controller such as the following sample:

`<?php defined('SYSPATH') OR die('No direct access allowed.');

class Sample_Controller extends Template_Controller {

  // Disable this controller when Kohana is set to production mode.
  // See http://docs.kohanaphp.com/installation/deployment for more details.
  const ALLOW_PRODUCTION = FALSE;

  // Set the name of the template to use
  public $template = 'kohana/template';

  public function __construct()
  {
    parent::__construct();
    
    $init = array(
      'api' => (Kohana::config('yboss.api')),
      'uri' => (Kohana::config('yboss.uri')),
      'format' => (Kohana::config('yboss.format')),
      'results' => '10',
    );
    $this->yboss = new Yboss($init);
  }
  
  public function search()
  {
    $this->template->content = new View('yboss/yboss_content');
    $this->template->title = 'Title';
    $this->template->content->content_types = array(
      'web' => 'Web',
      'images' => 'Images',
      'news' => 'News'
      );
    $this->template->content->selected_type = isset($_GET['type']) ? $_GET['type'] : 'web';

    if(isset($_GET['search'])){
      
      $this->template->title = 'Search Results';
      $page = isset($_GET['page']) ? $_GET['page'] : 0;
      $content = $this->yboss->query($_GET['search'], $_GET['type']);
      if($page > 0) $content  = $this->yboss->page( $page );
      $this->template->content->search_results = $content;
      $this->template->content->pager = $this->pager($content['page'], $content['pages']);
    }
    
  }
  
  private function pager($curr_page, $num_pages){
      //Pages
      $curr_url = "/yboss/search?type={$_GET['type']}&search={$_GET['search']}";
      $pager = array();
      
      //prev
      if($curr_page > 0)
        $pager[]= array('label'=>'&laquo;Prev', 'url'=>$curr_url. '&page=' .($curr_page - 1), 'extra'=>'');
      
      if($curr_page >=10){
        $first = $page-10;
        $last = $page;
      }else{
        $first = 1;
        $last = ($num_pages > 10) ? 10 : $num_pages;
      }
      for($i=$first; $i<$last; $i++){
        $extra = ($curr_page == ($i+1) )? ' class="current-page" ' : '';
        $pager[]= array('label'=>$i, 'url'=>$curr_url. '&page=' .$i , 'extra'=>$extra);
      }

      //next  
      $pager[]= array('label'=>'Next&raquo;', 'url'=>$curr_url. '&page=' .($curr_page + 1), 'extra'=>'');
      return $pager;
  }
  
  public function __call($method, $arguments)
  {
    // Disable auto-rendering
    $this->auto_render = FALSE;

    // By defining a __call method, all pages routed to this controller
    // that result in 404 errors will be handled by this method, instead of
    // being displayed as "Page Not Found" errors.
    echo __('This text is generated by __call. If you expected the index page, you need to use: :uri:',
        array(':uri:' => 'welcome/index/'.substr(Router::$current_uri, 8)));
  }

} // End Welcome Controller
`