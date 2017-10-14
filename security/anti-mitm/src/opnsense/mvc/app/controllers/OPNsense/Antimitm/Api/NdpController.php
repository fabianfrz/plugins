<?php

/*
 *    Copyright (C) 2015-2017 Deciso B.V.
 *    Copyright (C) 2015 Jos Schellevis
 *    Copyright (C) 2017 Fabian Franz
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 */

namespace OPNsense\Antimitm\API;

use \OPNsense\Antimitm\NDP;
use \OPNsense\Core\Config;
use \OPNsense\Base\ApiMutableModelControllerBase;
use \OPNsense\Base\UIModelGrid;

class NdpController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'ndp';
    static protected $internalModelClass = '\OPNsense\Antimitm\NDP';
    public function searchrouterAction()
    {
        $this->sessionClose();
        $mdl = $this->getModel();
        $grid = new UIModelGrid($mdl->router);
        return $grid->fetchBindRequest(
            $this->request,
            array('enabled', 'name')
        );
    }
    public function searchprefixAction()
    {
        $this->sessionClose();
        $mdl = $this->getModel();
        $grid = new UIModelGrid($mdl->prefix);
        return $grid->fetchBindRequest(
            $this->request,
            array('enabled', 'address', 'mask')
        );
    }
    public function getrouterAction($uuid = null)
    {
        $mdl = $this->getModel();
        if ($uuid != null) {
            $node = $mdl->getNodeByReference('router.' . $uuid);
            if ($node != null) {
                // return node
                return array('router' => $node->getNodes());
            }
        } else {
            $node = $mdl->router->add();
            return array('router' => $node->getNodes());
        }
        return array();
    }
    public function getprefixAction($uuid = null)
    {
        $mdl = $this->getModel();
        if ($uuid != null) {
            $node = $mdl->getNodeByReference('prefix.' . $uuid);
            if ($node != null) {
                // return node
                return array('prefix' => $node->getNodes());
            }
        } else {
            $node = $mdl->prefix->add();
            return array('prefix' => $node->getNodes());
        }
        return array();
    }
    public function addrouterAction()
    {
        $result = array('result' => 'failed');
        if ($this->request->isPost() && $this->request->hasPost('router')) {
            $result = array('result' => 'failed', 'validations' => array());
            $mdl = $this->getModel();
            $node = $mdl->router->Add();
            $node->setNodes($this->request->getPost('router'));
            $valMsgs = $mdl->performValidation();

            foreach ($valMsgs as $field => $msg) {
                $fieldnm = str_replace($node->__reference, 'router', $msg->getField());
                $result['validations'][$fieldnm] = $msg->getMessage();
            }

            if (count($result['validations']) == 0) {
                // save config if validated correctly
                $mdl->serializeToConfig();
                Config::getInstance()->save();
                unset($result['validations']);
                $result['result'] = 'saved';
            }
        }
        return $result;
    }
    public function addprefixAction()
    {
        $result = array('result' => 'failed');
        if ($this->request->isPost() && $this->request->hasPost('prefix')) {
            $result = array('result' => 'failed', 'validations' => array());
            $mdl = $this->getModel();
            $node = $mdl->prefix->Add();
            $node->setNodes($this->request->getPost('prefix'));
            $valMsgs = $mdl->performValidation();

            foreach ($valMsgs as $field => $msg) {
                $fieldnm = str_replace($node->__reference, 'prefix', $msg->getField());
                $result['validations'][$fieldnm] = $msg->getMessage();
            }

            if (count($result['validations']) == 0) {
                // save config if validated correctly
                $mdl->serializeToConfig();
                Config::getInstance()->save();
                unset($result['validations']);
                $result['result'] = 'saved';
            }
        }
        return $result;
    }

    public function delrouterAction($uuid)
    {

        $result = array('result' => 'failed');

        if ($this->request->isPost()) {
            $mdl = $this->getModel();
            if ($uuid != null) {
                if ($mdl->router->del($uuid)) {
                    $mdl->serializeToConfig();
                    Config::getInstance()->save();
                    $result['result'] = 'deleted';
                } else {
                    $result['result'] = 'not found';
                }
            }
        }
    }
    public function delprefixAction($uuid)
    {

        $result = array('result' => 'failed');

        if ($this->request->isPost()) {
            $mdl = $this->getModel();
            if ($uuid != null) {
                if ($mdl->prefix->del($uuid)) {
                    $mdl->serializeToConfig();
                    Config::getInstance()->save();
                    $result['result'] = 'deleted';
                } else {
                    $result['result'] = 'not found';
                }
            }
        }
        
        return $result;
    }
    public function setrouterAction($uuid)
    {
        if ($this->request->isPost() && $this->request->hasPost('router')) {
            $mdl = $this->getModel();
            if ($uuid != null) {
                $node = $mdl->getNodeByReference('router.' . $uuid);
                if ($node != null) {
                    $result = array('result' => 'failed', 'validations' => array());
                    $info = $this->request->getPost('router');

                    $node->setNodes($info);
                    $valMsgs = $mdl->performValidation();
                    foreach ($valMsgs as $field => $msg) {
                        $fieldnm = str_replace($node->__reference, 'router', $msg->getField());
                        $result['validations'][$fieldnm] = $msg->getMessage();
                    }

                    if (count($result['validations']) == 0) {
                        // save config if validated correctly
                        $mdl->serializeToConfig();
                        unset($result['validations']);
                        Config::getInstance()->save();
                        $result = array('result' => 'saved');
                    }
                    return $result;
                }
            }
        }
        return array('result' => 'failed');
    }
    public function setprefixAction($uuid)
    {
        if ($this->request->isPost() && $this->request->hasPost('prefix')) {
            $mdl = $this->getModel();
            if ($uuid != null) {
                $node = $mdl->getNodeByReference('prefix.' . $uuid);
                if ($node != null) {
                    $result = array('result' => 'failed', 'validations' => array());
                    $info = $this->request->getPost('prefix');

                    $node->setNodes($info);
                    $valMsgs = $mdl->performValidation();
                    foreach ($valMsgs as $field => $msg) {
                        $fieldnm = str_replace($node->__reference, 'prefix', $msg->getField());
                        $result['validations'][$fieldnm] = $msg->getMessage();
                    }

                    if (count($result['validations']) == 0) {
                        // save config if validated correctly
                        $mdl->serializeToConfig();
                        unset($result['validations']);
                        Config::getInstance()->save();
                        $result = array('result' => 'saved');
                    }
                    return $result;
                }
            }
        }
        return array('result' => 'failed');
    }
    public function toggle_handler($uuid, $element)
    {

        $result = array('result' => 'failed');

        if ($this->request->isPost()) {
            $mdl = $this->getModel();
            if ($uuid != null) {
                $node = $mdl->getNodeByReference($element . '.' . $uuid);
                if ($node != null) {
                    if ($node->enabled->__toString() == '1') {
                        $result['result'] = 'Disabled';
                        $node->enabled = '0';
                    } else {
                        $result['result'] = 'Enabled';
                        $node->enabled = '1';
                    }
                    $mdl->serializeToConfig();
                    Config::getInstance()->save();
                }
            }
        }
        return $result;
    }

    public function togglerouterAction($uuid)
    {
        return $this->toggle_handler($uuid, 'router');
    }
    public function toggleprefixAction($uuid)
    {
        return $this->toggle_handler($uuid, 'prefix');
    }
}
