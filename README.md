# Mautic Whatsapp Plugin
This plugin replaces the SMS channel and allows you to send messages to Whatsapp
using the self-hosted Whatsapp Web application server.
Orginally its Intended for >= Mautic 4.0, But i worked on mautic 5.1, So expected to work >= 5.1

Read more [here](https://joeykeller.com/weekend-project-a-mautic-whatsapp-plugin) for the History:

## Installation by console
1. Download the plugin, unzip in your plugins folder
2. Rename the folder to MauticWhatsappBundle
3. `php bin/console mautic:plugins:reload`

## Usage
1. Go to your **Plugins** in Mautic
2. You should see new Whatsapp plugin in the list, click and publish it.
3. Get [API Key](https://ravinayag.medium.com/mautic-digital-marketing-tool-859cd3ce0484) and see how you can get your credentials.
4. This plugin overrides your SMS transport. In your **Configuration > Text message settings** select Whatsapp as default transport

## DISCLAIMER OF WARRANTY
    BECAUSE THIS SOFTWARE IS LICENSED FREE OF CHARGE, THERE IS NO WARRANTY
    FOR THE SOFTWARE, TO THE EXTENT PERMITTED BY APPLICABLE LAW.
