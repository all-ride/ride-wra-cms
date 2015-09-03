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
     * @var Cms $cms
     */
    private $cms;


    /**
     * Get the nodes of all sites. The response is parsed to work with Redactor
     *
     * @param Cms $cms
     * @param Request $request
     * @param I18n $i18n
     * @param string $siteId
     * @param string $revision
     *
     * @return Response
     */
    public function urlVariablesAction(Cms $cms, Request $request, I18n $i18n, $siteId=null, $revision=null) {
        $this->cms = $cms;
        if (!$revision) {
            $revision = $this->cms->getDefaultRevision();
        }

        $sites = $this->cms->getSites();
        if ($siteId) {
            $sites = [$siteId=>$siteId];
        }
        $locale = $i18n->getLocale()->getCode();
        $nodes = [];

        foreach ($sites as $siteId=>$site) {
            // Get the nodes of this site and add them to the result array
            $siteNodes = $this->getNodesForSite($siteId, $revision, $locale);

            foreach ($siteNodes as $node) {
                $nodes[] = $node;
            }
        }

        $this->setJsonView($nodes);
    }

    /**
     * Parse the nodes of a site into an array
     *
     * @param string $siteId
     * @param string $revision
     * @param string $locale
     *
     * @return array
     */
    protected function getNodesForSite($siteId, $revision, $locale) {
        // Get all the nodes
        $nodes = $this->cms->getNodeModel()->getNodes($siteId, $revision);
        if (!count($nodes)) {
            // Set repsonse to 404 if no nodes were found
            return $this->response->setNotFound();
        }

        // Get the nodelist from the rootnode
        $rootNode = reset($nodes)->getRootNode();
        $nodeList = $this->cms->getNodeList($rootNode, $locale, true, true, false);
        $result = [];

        // Add each node to the result array if the ID is not null
        foreach ($nodeList as $id=>$url) {
            if ($id) {
                $nodeId = $nodes[$id]->getId();
                $result[] = [ 'name' => $url, 'url' => "[[page.$siteId.$nodeId.url.$locale]]" ];
            }
        }

        return $result;
    }

}
