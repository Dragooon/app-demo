<?php
namespace CSVTest\Controller;

use CSVTest\Controller\Exception\MissingFileException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Http\Response;
use Slim\Views\Twig;

class UploadController
{
    /**
     * @var Twig
     */
    protected $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function __invoke(ServerRequestInterface $request, Response $response)
    {
        try {
            $hash = hash('sha256', mt_rand());
            $files = $request->getUploadedFiles();
            $attributes = $request->getParsedBody();
            $type = $attributes['export-format'] == 'csv' ? 'csv' : 'xlsx';

            if (empty($files) || empty($files['file'])) {
                throw new MissingFileException('No file entered');
            }

            /** @var UploadedFileInterface $file */
            $file = $files['file'];
            $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
            $filename = $hash . '.' . $extension;
            $file->moveTo('cache/' . $filename);

            $phpExcel = \PHPExcel_IOFactory::load('cache/' . $filename);
            $data = $phpExcel->getActiveSheet()->toArray();
            $data = array_splice($data, 3);
            $output = [
                ['Year', 'Make', 'Model', 'MSRP', 'Sale Price', 'Savings off MSRP', '% Savings'],
            ];

            $current_row = [];
            foreach ($data as $row) {
                foreach ($row as $k => $value) {
                    if (!empty($value)) {
                        $current_row[$k] = $value;
                    }
                }

                $newRow = [];
                $keys = [2, 0, 1, 4, 3, 5];
                foreach ($keys as $destination => $current) {
                    $newRow[$destination] = $current_row[$current];
                }

                $newRow[6] = ((float) str_replace(',', '', $newRow[5]) / (float) str_replace(',', '', $newRow[3])) * 100;
                $newRow[6] = substr((string) $newRow[6], 0, strpos((string) $newRow[6], '.') + 3) . '%';
                $newRow[3] = '$' . $newRow[3];
                $newRow[4] = '$' . $newRow[4];
                $newRow[5] = '$' . $newRow[5];

                $output[] = $newRow;
            }
            
            $doc = new \PHPExcel();
            $doc->setActiveSheetIndex(0);
            $doc->getActiveSheet()->fromArray($output);
            @unlink('cache/' . $filename);
            \PHPExcel_IOFactory::createWriter($doc, $type == 'csv' ? 'CSV' : 'Excel2007')->save('cache/' . $hash . '.' . $type);

            return $response
                ->withJson([
                    'code' => $hash,
                ]);
        }
        catch (\Exception $e) {
            return $response
                ->withStatus(500)
                ->withJson([
                    'error' => $e->getMessage(),
                ]);
        }
    }
}