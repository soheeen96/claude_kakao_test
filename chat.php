<?php
// 헤더 설정
header('Content-Type: application/json; charset=utf-8');

// 메시지 저장 파일
$messagesFile = 'messages.json';

// 파일이 없으면 빈 배열로 초기화
if (!file_exists($messagesFile)) {
    file_put_contents($messagesFile, json_encode([]));
}

// 보안을 위한 함수
function sanitizeInput($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// 액션에 따른 처리
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'send':
        // 메시지 전송 처리
        if (isset($_POST['message']) && isset($_POST['nickname']) && isset($_POST['sessionId'])) {
            $message = sanitizeInput($_POST['message']);
            $nickname = sanitizeInput($_POST['nickname']);
            $sessionId = sanitizeInput($_POST['sessionId']);
            
            // 현재 메시지 목록 가져오기
            $messages = json_decode(file_get_contents($messagesFile), true);
            
            // 새 메시지 추가
            $newMessage = [
                'nickname' => $nickname,
                'message' => $message,
                'timestamp' => time(),
                'sessionId' => $sessionId
            ];
            
            $messages[] = $newMessage;
            
            // 메시지가 100개를 초과하면 오래된 메시지 삭제
            if (count($messages) > 100) {
                $messages = array_slice($messages, -100);
            }
            
            // 파일에 저장
            file_put_contents($messagesFile, json_encode($messages));
            
            echo json_encode(['status' => 'success']);
        }
        break;
        
    case 'enter':
        // 입장 메시지 처리
        if (isset($_POST['nickname']) && isset($_POST['sessionId'])) {
            $nickname = sanitizeInput($_POST['nickname']);
            $sessionId = sanitizeInput($_POST['sessionId']);
            
            // 현재 메시지 목록 가져오기
            $messages = json_decode(file_get_contents($messagesFile), true);
            
            // 입장 메시지 추가
            $enterMessage = [
                'nickname' => 'System',
                'message' => "「{$nickname}」님이 입장하셨습니다.",
                'timestamp' => time(),
                'sessionId' => 'system'
            ];
            
            $messages[] = $enterMessage;
            
            // 파일에 저장
            file_put_contents($messagesFile, json_encode($messages));
            
            echo json_encode(['status' => 'success']);
        }
        break;
        
    case 'fetch':
        // 메시지 목록 가져오기
        $messages = json_decode(file_get_contents($messagesFile), true);
        echo json_encode($messages);
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => '잘못된 요청입니다.']);
}
?>
