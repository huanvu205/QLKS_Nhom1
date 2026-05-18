<?php

class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(404);
            echo 'View not found';
            return;
        }

        require __DIR__ . '/../views/layout.php';
    }

    protected function redirect(string $page, array $params = []): void
    {
        $params = array_merge(['page' => $page], $params);
        header('Location: index.php?' . http_build_query($params));
        exit;
    }

    protected function post(string $name, mixed $default = ''): mixed
    {
        return $_POST[$name] ?? $default;
    }

    protected function get(string $name, mixed $default = ''): mixed
    {
        return $_GET[$name] ?? $default;
    }

    protected function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function requireLogin(): void
    {
        if (!$this->currentUser()) {
            $this->redirect('login');
        }
    }

    protected function requireRole(array $roles): void
    {
        $this->requireLogin();
        $role = $_SESSION['user']['VaiTro'] ?? '';
        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            $this->render('error', [
                'title' => 'Không có quyền',
                'active' => 'dashboard',
                'message' => 'Tài khoản hiện tại không có quyền thực hiện chức năng này.',
            ]);
            exit;
        }
    }

    protected function money(float|int|null $value): string
    {
        return number_format((float) $value, 0, ',', '.') . 'đ';
    }

    protected function tableRows(string $sql, array $params, array $columns): array
    {
        return array_map(static function (array $row) use ($columns) {
            return array_map(static fn ($col) => $row[$col] ?? '', $columns);
        }, Database::fetchAll($sql, $params));
    }
}
