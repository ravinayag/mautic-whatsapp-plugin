<?php
/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      Jan Kozak <galvani78@gmail.com>
 * @author      Ravinayag <ravinayag@gmail.com>
 */

namespace MauticPlugin\MauticWhatsappBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class WhatsappIntegration extends AbstractIntegration
{
    public function getName()
    {
        return 'Whatsapp';
    }

    public function getIcon()
    {
        return 'plugins/MauticWhatsappBundle/Assets/img/whatsapp.png';
    }

    public function getSecretKeys()
    {
        return ['apiKey'];
    }

    public function getRequiredKeyFields()
    {
        return [
            'apiKey' => 'mautic.plugin.whatsapp.apiKey',
            'apiUrl' => 'mautic.plugin.whatsapp.apiUrl',
        ];
    }

    public function getFormSettings(): array
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false,
        ];
    }

    public function getAuthenticationType()
    {
        return 'none';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigFormFields()
    {
        return [
            'apiKey' => [
                'label'      => 'mautic.plugin.whatsapp.apiKey',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.plugin.whatsapp.apiKey.tooltip',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'mautic.core.value.required',
                    ]),
                ],
                'type' => TextType::class,
            ],
            'apiUrl' => [
                'label'      => 'mautic.plugin.whatsapp.apiUrl',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.plugin.whatsapp.apiUrl.tooltip',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'mautic.core.value.required',
                    ]),
                    new Url([
                        'message' => 'mautic.core.valid_url_required',
                    ]),
                ],
                'type' => UrlType::class,
            ],
        ];
    }
}
