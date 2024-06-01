
<?php

class GoogleReaderAPI {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function handleRequest() {
        $endpoint = $_GET['endpoint'] ?? '';

        switch ($endpoint) {
            case 'subscription/list':
                $this->listSubscriptions();
                break;
            case 'stream/contents':
                $this->getStreamContents();
                break;
            case 'edit-tag':
                $this->editTag();
                break;
            default:
                $this->sendError('Unknown endpoint');
                break;
        }
    }

    private function listSubscriptions() {
        try {
            $sth = $this->pdo->prepare("SELECT id, title, url FROM subscriptions");
            $sth->execute();
            $subscriptions = $sth->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['subscriptions' => $subscriptions]);
        } catch (PDOException $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function getStreamContents() {
        $streamId = $_GET['streamId'] ?? '';
        if (empty($streamId)) {
            $this->sendError('streamId is required');
            return;
        }

        try {
            $sth = $this->pdo->prepare("SELECT id, title, content, url, published FROM contents WHERE stream_id = ?");
            $sth->execute([$streamId]);
            $contents = $sth->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['contents' => $contents]);
        } catch (PDOException $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function editTag() {
        $itemId = $_POST['i'] ?? '';
        $tag = $_POST['a'] ?? '';

        if (empty($itemId) || empty($tag)) {
            $this->sendError('itemId and tag are required');
            return;
        }

        try {
            if ($tag === 'user/-/state/com.google/read') {
                $sth = $this->pdo->prepare("UPDATE contents SET is_read = 1 WHERE id = ?");
            } else {
                $sth = $this->pdo->prepare("UPDATE contents SET is_read = 0 WHERE id = ?");
            }
            $sth->execute([$itemId]);
            echo json_encode(['result' => 'success']);
        } catch (PDOException $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function sendError($message) {
        echo json_encode(['error' => $message]);
    }
}

?>
