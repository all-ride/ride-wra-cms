<?php

namespace ride\wra\cms\controller;

use ride\library\http\Request;
use ride\library\i18n\I18n;
use ride\web\cms\Cms;
use ride\web\base\controller\AbstractController;

/**
 * SiteController
 */
class SiteController extends AbstractController {

    /**
     * Get the nodes of a site and an optional revision. The response is parsed to work with Redactor
     *
     * @param Cms $cms
     * @param Request $request
     * @param I18n $i18n
     * @param string $siteId
     * @param string $revision
     *
     * @return Response
     */
    public function siteNodesAction(Cms $cms, Request $request, I18n $i18n, $siteId, $revision=null) {
        // Get the default revision if not set
        if (!$revision) {
            $revision = $cms->getDefaultRevision();
        }

        // Get all the nodes
        $nodes = $cms->getNodeModel()->getNodes($siteId, $revision);
        if (!count($nodes)) {
            // Set repsonse to 404 if no nodes were found
            return $this->response->setNotFound();
        }

        // Get the nodelist from the rootnode
        $rootNode = reset($nodes)->getRootNode();
        $nodeList = $cms->getNodeList($rootNode, $i18n->getLocale()->getCode());
        $result = [];
        // Add each node to the result array if the ID is not null
        foreach ($nodeList as $id=>$url) {
            if ($id) {
                $result[] = [ 'name' => $url, 'url' => '[[node.'.$nodes[$id]->getId().'.url]]' ];
            }
        }
        // Sort the nodes by url
        sort($result);

        $this->setJsonView($result);
    }

}
