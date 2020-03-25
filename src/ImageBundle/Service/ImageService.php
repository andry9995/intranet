<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 22/02/2019
 * Time: 09:54
 */

namespace ImageBundle\Service;


use AppBundle\Entity\Dossier;
use AppBundle\Entity\Image;
use Doctrine\ORM\EntityManager;

class ImageService
{
    private $entity_manager;

    public function __construct(EntityManager $em)
    {
        $this->entity_manager = $em;
    }

    public function getUrl($imageid){
        $url = '';

        /** @var Image $image */
        $image = $this->entity_manager
            ->getRepository('AppBundle:Image')
            ->find($imageid);

        if($image){

            if($image->getSourceImage()->getSource()=== 'IMPORT SANS IMAGE'){
                return  'http://192.168.0.9/intranet%20images/IMAGE_FANTOME/IMAGE.pdf';
            }

            /** @var Dossier $dossier */
            $dossier = $image->getLot()->getDossier();

            $dateScanFomated = $image->getLot()->getDateScan()->format('Ymd');

//            $url = 'https://www.lesexperts.biz/IMAGES/' . $dateScanFomated . '/' . $image->getNom() . '.'.$image->getExtImage();
//
//            return $url;

            for ($i = $image->getExercice()- 1; $i<= $image->getExercice()+ 1; $i++) {
                $url = 'http://192.168.0.9/Images%20comptabilis%C3%A9es/' .
                    $dossier->getSite()->getClient()->getNom() . '/' .
                    $dossier->getNom() . '/' .
                    $i . '/' .
                    $image->getNom() . '.pdf';

                $file_headers = get_headers($url);
                if ($file_headers[0] == 'HTTP/1.1 200 OK') {
                    return $url;
                }
            }


            $url = 'http://192.168.0.9/Images%20comptabilis%C3%A9es/'.
                $dossier->getSite()->getClient()->getNom().'/'.
                $dossier->getNom().'/'.
                ($image->getExercice() - +1).'/'.
                $image->getNom().'.pdf';

            $file_headers = get_headers($url);
            if ($file_headers[0] == 'HTTP/1.1 200 OK') {
                return $url;
            }


            $lot = $image->getLot();

            $url = 'http://192.168.0.9/intranet%20images/IMAGES_A_TRAITER/'.
                $dossier->getSite()->getClient()->getNom().'/'.
                $dossier->getNom().'/'.
                $image->getExercice().'/'.
                $lot->getDateScan()->format('Y-m-d').'/'.
                $lot->getLot().'/'.
                $image->getNom().'.pdf';

            $file_headers = get_headers($url);
            if ($file_headers[0] == 'HTTP/1.1 200 OK') {
                return $url;
            }

            $url = 'http://192.168.0.9/intranet%20images/' . $dateScanFomated . '/' . $image->getNom() . '.pdf';
            $file_headers = get_headers($url);
            if ($file_headers[0] == 'HTTP/1.1 200 OK') {
                return $url;
            }

           $url = 'http://192.168.0.5/IMAGES/'.$dateScanFomated.'/'.$image->getNom().'.pdf';
           $file_headers = get_headers($url);
           if($file_headers[0] == 'HTTP/1.1 200 OK'){
               return $url;
           }

            //COPY IMAGE FROM NAS -> LOCAL
            //Images Tsotra
            if (!file_exists('NAS/'.$dateScanFomated)) {
                mkdir('NAS/'.$dateScanFomated, 0777, true);
            }

            $local_file =  'NAS/'.$dateScanFomated.'/'.$image->getNom().'.pdf';

            $server_file = '/'.$dateScanFomated.'/'.$image->getNom().'.pdf';
            $ftp_server="192.168.0.60";
            $ftp_user_name="ftp_nas";
            $ftp_user_pass="n@s20";
            $conn_id = ftp_connect($ftp_server);
            ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
            ftp_pasv($conn_id, true);

            if(file_exists($local_file)){
                $url = 'http://'.$_SERVER['SERVER_NAME'].'/NAS/' . $dateScanFomated . '/' . $image->getNom() . '.pdf';
                return $url;
            }

            $file_size = ftp_size($conn_id, $server_file);

            if($file_size != -1) {
                if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
                    $url = 'http://' . $_SERVER['SERVER_NAME'] . '/NAS/' . $dateScanFomated . '/' . $image->getNom() . '.pdf';
                    $file_headers = get_headers($url);
                    if ($file_headers[0] == 'HTTP/1.1 200 OK') {
                        ftp_close($conn_id);
                        return $url;
                    }
                }
                else{
                  echo "download error \n";
                }
            }
            ftp_close($conn_id);


            //Images comptabilisées


            $ftp_server="192.168.0.60";
            $ftp_user_name="ftp_imaz";
            $ftp_user_pass="im@z20";
            $conn_id = ftp_connect($ftp_server);
            ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
            ftp_pasv($conn_id, true);

            if(file_exists($local_file)){
                $url = 'http://'.$_SERVER['SERVER_NAME'].'/NAS/' . $dateScanFomated . '/' . $image->getNom() . '.pdf';
                return $url;
            }

            for ($i = $image->getExercice()- 1; $i<= $image->getExercice()+ 1; $i++) {
                $server_file =
                    $dossier->getSite()->getClient()->getNom() . '/' .
                    $dossier->getNom() . '/' .
                    $i . '/' .
                    $image->getNom() . '.pdf';

                $file_size = ftp_size($conn_id, $server_file);

                if($file_size != -1) {
                    if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
                        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/NAS/' . $dateScanFomated . '/' . $image->getNom() . '.pdf';
                        $file_headers = get_headers($url);
                        if ($file_headers[0] == 'HTTP/1.1 200 OK') {
                            ftp_close($conn_id);
                            return $url;
                        }
                    }
                    else{
                        echo "download error \n";
                    }

                }
            }
            ftp_close($conn_id);


            $url = 'https://www.lesexperts.biz/IMAGES/' . $dateScanFomated . '/' . $image->getNom() . '.pdf';
            $file_headers = get_headers($url);
            if ($file_headers[0] == 'HTTP/1.1 200 OK') {
                ftp_close($conn_id);
                return $url;
            }


            return $url;
        }

        return $url;
    }

    /**
     * @param $FromLocation
     * @param $ToLocation
     * @param bool $VerifyPeer
     * @param int $VerifyHost
     * @return bool
     */
    function copySecureFile($FromLocation, $ToLocation, $VerifyPeer = false, $VerifyHost = 2)
    {
        $Channel = curl_init($FromLocation);
        $File = fopen($ToLocation, "w");
        curl_setopt($Channel, CURLOPT_FILE, $File);
        curl_setopt($Channel, CURLOPT_HEADER, 0);
        curl_setopt($Channel, CURLOPT_SSL_VERIFYPEER, $VerifyPeer);
        curl_setopt($Channel, CURLOPT_SSL_VERIFYHOST, $VerifyHost);
        curl_exec($Channel);
        curl_close($Channel);
        fclose($File);
        return file_exists($ToLocation);
    }

}