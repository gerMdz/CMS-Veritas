<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class ObtenerDatosHelper
{
    private $requestStack;

    /**
     * ObtenerDatosHelper constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getIpCliente(): string
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if ('' != $_SERVER['HTTP_X_FORWARDED_FOR']) {
                $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ?
                    $_SERVER['REMOTE_ADDR'] :
                    ((!empty($_ENV['REMOTE_ADDR'])) ?
                        $_ENV['REMOTE_ADDR'] :
                        'unknown');

                // los proxys van añadiendo al final de esta cabecera
                // las direcciones ip que van "ocultando". Para localizar la ip real
                // del usuario se comienza a mirar por el principio hasta encontrar
                // una dirección ip que no sea del rango privado. En caso de no
                // encontrarse ninguna se toma como valor el REMOTE_ADDR

                $entries = preg_split('/[, ]/', $_SERVER['HTTP_X_FORWARDED_FOR']);

                reset($entries);
                while (list(, $entry) = each($entries)) {
                    $entry = trim($entry);
                    if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list)) {
                        // http://www.faqs.org/rfcs/rfc1918.html
                        $private_ip = [
                            '/^0\./',
                            '/^127\.0\.0\.1/',
                            '/^192\.168\..*/',
                            '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                            '/^10\..*/', ];

                        $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

                        if ($client_ip != $found_ip) {
                            $client_ip = $found_ip;
                            break;
                        }
                    }
                }
            } else {
                $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ?
                    $_SERVER['REMOTE_ADDR'] :
                    ((!empty($_ENV['REMOTE_ADDR'])) ?
                        $_ENV['REMOTE_ADDR'] :
                        'unknown');
            }
        } else {
            $client_ip = $this->requestStack->getCurrentRequest()->getClientIps();
        }

        return $client_ip[0];
    }

    public function getIds($data): array
    {
        return array_values(
            array_map(
                function ($d) {
                    return $d->getId();
                }, $data
            )
        );
    }
}
