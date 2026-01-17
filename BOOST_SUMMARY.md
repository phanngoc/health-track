# Laravel Boost - Tóm tắt cơ chế hoạt động

## Kiến trúc

### 1. MCP (Model Context Protocol) Server
Boost hoạt động như một MCP server, cho phép AI agents giao tiếp với Laravel application thông qua một protocol chuẩn.

```
┌─────────────┐         MCP Protocol         ┌──────────────┐
│  IDE/AI     │ ────────────────────────────> │  boost:mcp   │
│  Agent      │ <──────────────────────────── │  (MCP Server)│
└─────────────┘                               └──────────────┘
                                                      │
                                                      ▼
                                              ┌──────────────┐
                                              │  ToolRegistry │
                                              │  (15+ tools)  │
                                              └──────────────┘
                                                      │
                                                      ▼
                                              ┌──────────────┐
                                              │ Laravel App  │
                                              │  (Database,   │
                                              │   Routes,    │
                                              │   Config...) │
                                              └──────────────┘
```

### 2. Tool Discovery
Tools được tự động discover từ thư mục `vendor/laravel/boost/src/Mcp/Tools/`:

```php
// ToolRegistry tự động scan và đăng ký tất cả tools
$toolDir = new DirectoryIterator(__DIR__.'/Tools');
foreach ($toolDir as $toolFile) {
    if ($toolFile->isFile() && $toolFile->getExtension() === 'php') {
        $fqdn = 'Laravel\\Boost\\Mcp\\Tools\\'.$toolFile->getBasename('.php');
        // Register tool...
    }
}
```

### 3. Tool Execution
Mỗi tool extends class `Tool` và implement method `handle()`:

```php
class DatabaseQuery extends Tool
{
    protected string $description = 'Execute a read-only SQL query...';
    
    public function handle(Request $request): Response
    {
        // Validate query is read-only
        // Execute query
        // Return results
        return Response::json($results);
    }
}
```

## Các Tools chính

### Application Introspection
1. **ApplicationInfo** - PHP/Laravel version, installed packages
2. **GetConfig** - Read config values
3. **ListAvailableConfigKeys** - List all config keys
4. **ListAvailableEnvVars** - List environment variables
5. **GetAbsoluteUrl** - Get app URL

### Database
6. **DatabaseConnections** - List database connections
7. **DatabaseSchema** - Get database schema
8. **DatabaseQuery** - Execute read-only SQL queries

### Routing & Commands
9. **ListRoutes** - List all routes with middleware/controllers
10. **ListArtisanCommands** - List all Artisan commands

### Debugging
11. **Tinker** - Execute PHP code in Laravel context
12. **ReadLogEntries** - Read application logs
13. **BrowserLogs** - Read browser console logs
14. **LastError** - Get last application error

### Documentation
15. **SearchDocs** - Search Laravel documentation (17,000+ pieces)

## Cơ chế bảo mật

### Read-only Operations
- Database queries chỉ cho phép SELECT, SHOW, EXPLAIN, DESCRIBE
- Không cho phép INSERT, UPDATE, DELETE, DROP, etc.

```php
// DatabaseQuery.php
$allowList = ['SELECT', 'SHOW', 'EXPLAIN', 'DESCRIBE', 'DESC', 'WITH', 'VALUES', 'TABLE'];
if (!in_array($firstWord, $allowList)) {
    return Response::error('Only read-only queries allowed');
}
```

### Tool Annotations
Tools có thể được đánh dấu là read-only:
```php
#[IsReadOnly]
class DatabaseQuery extends Tool { }
```

## Guidelines System

### Auto-detection
Khi chạy `boost:install`, Boost sẽ:
1. Detect packages đã cài (Laravel, Livewire, Inertia, etc.)
2. Tự động include guidelines phù hợp với versions
3. Generate guideline files cho từng AI agent

### Composable Guidelines
Guidelines được compose từ nhiều nguồn:
- Foundation rules (PHP, Laravel core)
- Package-specific rules (Livewire, Inertia, etc.)
- Custom project guidelines (`.ai/guidelines/`)

## Workflow thực tế

### Khi AI Agent cần thông tin:

1. **Query Database Schema**
   ```
   AI: "What tables exist in the database?"
   → Calls: database-schema tool
   → Returns: List of tables with columns
   ```

2. **Check Routes**
   ```
   AI: "What routes are available?"
   → Calls: list-routes tool
   → Returns: All routes with methods, URIs, controllers
   ```

3. **Search Documentation**
   ```
   AI: "How to use rate limiting?"
   → Calls: search-docs tool with query="rate limiting"
   → Returns: Version-specific Laravel docs
   ```

4. **Execute Code**
   ```
   AI: "Check if User model exists"
   → Calls: tinker tool with code="User::count()"
   → Returns: Result of execution
   ```

## Lợi ích chính

1. **Context Awareness**: AI hiểu rõ cấu trúc app của bạn
2. **Version Specific**: Docs và guidelines match với versions bạn dùng
3. **Safe**: Read-only operations, không thể phá hoại
4. **Extensible**: Có thể thêm custom tools và guidelines
5. **IDE Integration**: Tích hợp sẵn với Cursor, VS Code, Claude Code, etc.

## Test nhanh

Để test MCP server (sẽ chạy và chờ requests):
```bash
php artisan boost:mcp
```

Server sẽ:
- Khởi tạo Laravel app
- Load tất cả tools
- Listen trên stdio cho MCP requests
- Trả về JSON responses theo MCP protocol

## Kết luận

Laravel Boost là một bridge mạnh mẽ giữa AI agents và Laravel applications, cho phép AI:
- Hiểu cấu trúc ứng dụng
- Query database an toàn
- Đọc routes, config, logs
- Tìm kiếm documentation version-specific
- Execute code trong Laravel context

Tất cả được thực hiện qua MCP protocol, một chuẩn mở cho AI-tool communication.

