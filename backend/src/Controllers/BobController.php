<?php

require_once __DIR__ . '/../Models/BobProduct.php';

class BobController
{
    private $bobProductModel;

    public function __construct($pdo)
    {
        $this->bobProductModel = new BobProduct($pdo);
    }

    public function search()
    {
        try {
            $filters = [
                'ean'         => $_GET['ean']         ?? null,
                'libelle'     => $_GET['libelle']     ?? null,
                'fournisseur' => $_GET['fournisseur'] ?? null,
            ];

            $page = isset($_GET['page']) && is_numeric($_GET['page'])
                ? (int) $_GET['page']
                : 1;

            $result = $this->bobProductModel->search($filters, $page);

            $this->sendResponse(200, [
                'error' => false,
                'count' => count($result['data']),
                'data'  => $result['data'],
                'total' => $result['total'],
                'page'  => $result['page'],
                'limit' => $result['limit'],
            ]);
        } catch (PDOException $e) {
            error_log('BobController::search - ' . $e->getMessage());
            $this->sendResponse(500, [
                'error'   => true,
                'message' => 'Erreur interne du serveur',
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error'   => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function sendResponse($statusCode, $data)
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}