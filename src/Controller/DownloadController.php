<?php
namespace CSVTest\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class DownloadController
{
    /**
     * @var Twig
     */
    protected $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        try {
            if (!empty($args['code'])) {
                $file = 'cache/' . $args['code'];

                $mime = pathinfo($args['code'], PATHINFO_EXTENSION) == 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

                $response = $response
                    ->withAddedHeader('Content-type', $mime)
                    ->withAddedHeader('Content-disposition', 'attachment; filename="output.' . pathinfo($args['code'], PATHINFO_EXTENSION) . '"')
                    ->withAddedHeader('Content-length', filesize($file));
                $response->getBody()->write(file_get_contents($file));
                return $response;
            }
        }
        catch (\Exception $e) {
        }
    }
}