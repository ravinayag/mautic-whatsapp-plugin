<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticWhatsappBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MauticWhatsappBundle.
 */
class MauticWhatsappBundle extends PluginBundleBase
{
    /**
 *      * {@inheritdoc}
 *           */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }

    /**
 *      * {@inheritdoc}
 *           */
    public function boot()
    {
        parent::boot();
    }

    /**
 *      * {@inheritdoc}
 *           */
    public function getParent()
    {
        return 'MauticSmsBundle';
    }
}
