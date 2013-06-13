<?
require_once 'app/controllers/studip_controller.php';

class ApplicationController extends StudipController{

    function __construct($dispatcher) {
        parent::__construct($dispatcher);
        $this->plugin = $this->dispatcher->current_plugin;
    }

    function before_filter($action, $args) {

        $this->current_action = $action;
        $this->flash = Trails_Flash::instance();
        $this->standard_templates = $GLOBALS['STUDIP_BASE_PATH'] . '/templates/';
        $this->standard_trails_root = $GLOBALS['STUDIP_BASE_PATH'] . '/app/';
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
        PageLayout::setTitle($this->plugin->getDisplayTitle());
        $this->url = $this->plugin->getPluginUrl(). '/public/';
        PageLayout::addHeadElement('link', array("rel"  => "stylesheet",
                                           "href" => $this->url . "stylesheets/application.css",
                                           "type" => "text/css"
                                           ));
        PageLayout::addHeadElement('script', array("src"  => $this->url . "javascripts/application.js",
                                             "type" => "text/javascript"
                                           ));
        $this->validate_args($args);
    }

    public function is_xhr()
    {
        return $_SERVER['HTTP_X_REQUESTED_WITH'] &&
            strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') === 0;
    }

    function after_filter($action, $args) {
        page_close();
    }

    function url_for($to = '', $params = array()) {
        if($to === '') {
            $to = substr(strtolower(get_class($this)), 0, -10) . '/' . $this->current_action;
        }
        $url = PluginEngine::getUrl($this->plugin, $params, $to);
        return $url;
    }

    function link_for($to = '', $params = array()) {
        if($to === '') {
            $to = substr(strtolower(get_class($this)), 0, -10) . '/' . $this->current_action;
        }
        return PluginEngine::getLink($this->plugin, $params, $to);
    }

    function flash_set($type, $message, $submessage = array()){
        $msg = call_user_func(array('MessageBox',$type), $message, (array)$submessage);
        PageLayout::postMessage($msg);
    }

    function flash_now($type, $message, $submessage = array()){
        $this->flash_set($type, $message, $submessage);
    }

    function render_json($data){
        $this->set_content_type('application/json;charset=utf-8');
        return $this->render_text(json_encode($data));
    }
}

