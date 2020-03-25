<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new AppBundle\AppBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new UtilisateurBundle\UtilisateurBundle(),
            new ModelBundle\ModelBundle(),
            new AdminBundle\AdminBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new MenuBundle\MenuBundle(),
            new DashboardBundle\DashboardBundle(),
            new AdminUserBundle\AdminUserBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
            new Ensepar\Html2pdfBundle\EnseparHtml2pdfBundle(),
            new Sonata\IntlBundle\SonataIntlBundle(),
            new BanqueBundle\BanqueBundle(),
            new ParametreBundle\ParametreBundle(),
            new ReceptionBundle\ReceptionBundle(),
            new TenueBundle\TenueBundle(),
            new RevisionBundle\RevisionBundle(),
            new PilotageBundle\PilotageBundle(),
            new TacheBundle\TacheBundle(),
            new ProcedureBundle\ProcedureBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new PrioriteBundle\PrioriteBundle(),
            new GammeBundle\GammeBundle(),
            new ImageBundle\ImageBundle(),
            new AjaxLoginBundle\AjaxLoginBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();

            if ('dev' === $this->getEnvironment()) {
                $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
                $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
            }
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
