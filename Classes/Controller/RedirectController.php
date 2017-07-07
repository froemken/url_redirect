<?php
namespace StefanFroemken\UrlRedirect\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use StefanFroemken\UrlRedirect\Domain\Model\Config;
use StefanFroemken\UrlRedirect\Domain\Repository\ConfigRepository;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class PreProcess
 *
 * @package StefanFroemken\UrlRedirect\Controller
 */
class RedirectController extends ActionController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * inject configRepository
     *
     * @param ConfigRepository $configRepository
     *
     * @return void
     */
    public function injectConfigRepository(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * List action
     *
     * @return void
     */
    public function listAction()
    {
        $configurations = $this->configRepository->findAll();
        $this->view->assign('configurations', $configurations);
    }

    /**
     * New action
     *
     * @return void
     */
    public function newAction()
    {
        /** @var Config $config */
        $config = $this->objectManager->get(Config::class);
        $this->view->assign('config', $config);
        $this->view->assign('httpStatus', $this->configRepository->getHttpStatus());
    }

    /**
     * Create action
     *
     * @param Config $config
     *
     * @return void
     */
    public function createAction(Config $config)
    {
        $config->setPid(0);
        $this->configRepository->add($config);
        $this->addFlashMessage('Configuration was saved successfully');
        $this->redirect('list');
    }

    /**
     * Edit action
     *
     * @param Config $config
     *
     * @return void
     */
    public function editAction(Config $config)
    {
        $this->view->assign('config', $config);
        $this->view->assign('httpStatus', $this->configRepository->getHttpStatus());
    }

    /**
     * Update action
     *
     * @param Config $config
     *
     * @return void
     */
    public function updateAction(Config $config)
    {
        $config->setPid(0);
        $this->configRepository->update($config);
        $this->addFlashMessage('Configuration was updated successfully');
        $this->redirect('list');
    }

    /**
     * Delete action
     *
     * @param int $config
     *
     * @return void
     */
    public function DeleteAction($config)
    {
        /** @var Config $configObject */
        $configObject = $this->configRepository->findByIdentifier((int)$config);
        $this->addFlashMessage(
            'Are you sure you want to delete this redirect record?',
            'Delete record',
            AbstractMessage::WARNING
        );
        $this->view->assign('config', $configObject);
    }

    /**
     * Do Delete action
     *
     * @param int $config
     *
     * @return void
     */
    public function doDeleteAction($config)
    {
        /** @var Config $configObject */
        $configObject = $this->configRepository->findByIdentifier((int)$config);
        $this->configRepository->remove($configObject);
        $this->addFlashMessage('Configuration has been removed');
        $this->redirect('list');
    }
}
