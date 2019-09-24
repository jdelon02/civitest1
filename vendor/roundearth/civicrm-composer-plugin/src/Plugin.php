<?php

namespace Roundearth\CivicrmComposerPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Composer plugin to add support for CiviCRM.
 */
class Plugin implements PluginInterface, EventSubscriberInterface, Capable {

  /**
   * @var \Roundearth\CivicrmComposerPlugin\Handler
   */
  protected $handler;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    $filesystem = new Filesystem();
    $util = new Util($filesystem);
    $this->handler = new Handler($composer, $io, $filesystem, $util);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PackageEvents::POST_PACKAGE_INSTALL => 'onPackageInstallOrUpdate',
      PackageEvents::POST_PACKAGE_UPDATE => 'onPackageInstallOrUpdate',
    ];
  }

  /**
   * Event callback for either the install or update package events.
   *
   * @param \Composer\Installer\PackageEvent $event
   *   The event.
   */
  public function onPackageInstallOrUpdate(PackageEvent $event) {
    /** @var \Composer\DependencyResolver\Operation\InstallOperation|\Composer\DependencyResolver\Operation\UpdateOperation $operation */
    $operation = $event->getOperation();

    $package = method_exists($operation, 'getTargetPackage')
        ? $operation->getTargetPackage()
        : $operation->getPackage();

    $name = $package->getName();

    if ($name == 'civicrm/civicrm-core') {
      $this->handler->afterCivicrmInstallOrUpdate($package);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCapabilities() {
    return [
      'Composer\Plugin\Capability\CommandProvider' => CommandProvider::class,
    ];
  }


}