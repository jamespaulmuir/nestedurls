<?php
/**
 * Nested Urls SilverStripe Module
 * NestUrlController (based on ModelAsController)
 * @author James Muir <james.p.muir@gmail.com>
 * @copyright Copyright (c) 2009, James Muir
 */
class NestedUrlController extends ModelAsController {

	public function handleRequest($request) {
		$this->pushCurrent();
		$this->urlParams = $request->allParams();
        $this->request = $request;
		$this->init();

		// If the basic database hasn't been created, then build it.
		if(!DB::isActive() || !ClassInfo::hasTable('SiteTree')) {
			$this->response = new HTTPResponse();
			$this->redirect("dev/build?returnURL=" . (isset($_GET['url']) ? urlencode($_GET['url']) : ''));
			$this->popCurrent();
			return $this->response;
		}
       
		$result = $this->getNestedController();

		if(is_object($result) && $result instanceOf RequestHandler) {
			$result = $result->handleRequest($request);
		}

		$this->popCurrent();
		return $result;
	}

	public function getNestedController() {
        $url = Director::urlParams();
        $hasUrlSegment = false;
        $parent = null;
        $counter = 1;
        foreach($this->urlParams as $urlSegment){
            $hasUrlSegment = true;
            $SQL_URLSegment = Convert::raw2sql($urlSegment);
            $filter = "URLSegment = '$SQL_URLSegment'";
            if(isset($child)){
                $filter .= "AND ParentID = {$child->ID}";
            }
            else {
                $filter .= "AND ParentID = 0";
            }

            $child = DataObject::get_one('SiteTree', $filter);
            
            if(!$child){
               $key = $counter-2;
               if($key < 0) $key = 0;
               $this->mapParams($key);
               $c = new ModelAsController();
               $this->request->setParams($this->urlParams);
               return $c->handleRequest($this->request);

            }

            if(isset($child->UrlParentID) && $child->UrlParentID != 0){
                $parent = $child->UrlParent();
            }
            else if(isset($child->ParentID) && $child->ParentID != 0){
                $parent = $child->Parent();
            }
            else{
                $parent = null;
            }

            if($parent && $parent->URLSegment != 'home'){
                $keyint = $counter-1 ;
                $key = "url". $keyint;
                  if(!isset($this->urlParams[$key]) || $parent->URLSegment != $this->urlParams[$key]){
                     $child = $this->get404Page();
                     break;
                  }
            }

            $counter++;
        }
		if($hasUrlSegment){

			if(!$child) {
				if($child = $this->findOldPage($SQL_URLSegment)) {
					$url = Controller::join_links(
						Director::baseURL(),
						$child->URLSegment,
						(isset($this->urlParams['Action'])) ? $this->urlParams['Action'] : null,
						(isset($this->urlParams['ID'])) ? $this->urlParams['ID'] : null,
						(isset($this->urlParams['OtherID'])) ? $this->urlParams['OtherID'] : null
					);

					$response = new HTTPResponse();
					$response->redirect($url, 301);
					return $response;
				}

				$child = $this->get404Page();
			}

			if($child) {
                
                $this->mapParams();
				if(isset($_REQUEST['debug'])) Debug::message("Using record #$child->ID of type $child->class with URL {$this->urlParams['URLSegment']}");

				// set language
				if($child->Locale) Translatable::set_current_locale($child->Locale);

				$controllerClass = "{$child->class}_Controller";

				if($this->urlParams['Action'] && ClassInfo::exists($controllerClass.'_'.$this->urlParams['Action'])) {
					$controllerClass = $controllerClass.'_'.$this->urlParams['Action'];
				}

				if(ClassInfo::exists($controllerClass)) {
					$controller = new $controllerClass($child);
				} else {
					$controller = $child;
				}

				return $controller;
			} else {
				return new HTTPResponse("The requested page couldn't be found.",404);
			}

		} else {
			user_error("NestedUrlController not geting a URLSegment.  It looks like the site isn't redirecting to home", E_USER_ERROR);
		}
	}


    protected function mapParams($key = 0){
        $urlParams = array_slice($this->urlParams, $key);
        foreach($urlParams as $param){
            $params[] = $param;
        }
        $this->urlParams = array(
            'URLSegment'=>$params[0],
            'Action'=>$params[1],
            'ID'=>$params[2],
            'OtherID'=>$params[3]

        );
    }
}

?>