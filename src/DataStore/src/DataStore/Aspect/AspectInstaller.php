<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 13.03.17
 * Time: 11:32
 */

namespace rollun\datastore\DataStore\Aspect;

use rollun\datastore\DataStore\Aspect\Factory\AspectAbstractFactory;
use rollun\installer\Install\InstallerAbstract;

class AspectInstaller extends InstallerAbstract
{

    /**
     * install
     * @return array
     */
    public function install()
    {
        return [
            'dependencies' => [
                'abstract_factories' => [
                    AspectAbstractFactory::class,
                ],
            ]
        ];
    }

    /**
     * Clean all installation
     * @return void
     */
    public function uninstall()
    {

    }

    /**
     * Return string with description of installable functional.
     * @param string $lang ; set select language for description getted.
     * @return string
     */
    public function getDescription($lang = "en")
    {
        switch ($lang) {
            case "ru":
                $description = "Позволяет обращаться к удалеенному хранилищу по http";
                break;
            default:
                $description = "Does not exist.";
        }
        return $description;
    }

    public function isInstall()
    {
        $config = $this->container->get('config');
        return (isset($config['dependencies']['abstract_factories']) &&
            in_array(AspectAbstractFactory::class, $config['dependencies']['abstract_factories']));
    }
}
