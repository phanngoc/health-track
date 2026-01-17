# Laravel Boost - Hướng dẫn và Demo

## Tổng quan

Laravel Boost là một MCP (Model Context Protocol) server cung cấp các công cụ mạnh mẽ để AI agents hiểu và tương tác với ứng dụng Laravel của bạn.

## Cấu trúc cài đặt

Sau khi chạy `php artisan boost:install`, các file sau đã được tạo:

### 1. File cấu hình MCP (`.mcp.json`)
```json
{
    "mcpServers": {
        "laravel-boost": {
            "command": "php",
            "args": ["artisan", "boost:mcp"]
        }
    }
}
```
File này cho phép IDE (như Cursor, VS Code, Claude Code) kết nối với MCP server.

### 2. File guidelines cho AI agents
- `CLAUDE.md` - Guidelines cho Claude
- `GEMINI.md` - Guidelines cho Gemini  
- `AGENTS.md` - Guidelines cho các agents khác

Các file này chứa:
- Quy tắc coding conventions
- Hướng dẫn sử dụng Laravel
- Cách sử dụng các Boost tools
- Best practices cho Laravel development

### 3. File cấu hình Boost (`boost.json`)
```json
{
    "agents": ["claude_code", "codex", "cursor", "gemini"],
    "editors": ["claude_code", "codex", "cursor", "gemini", "vscode"],
    "guidelines": []
}
```

## Các Tools có sẵn

Boost cung cấp hơn 15 tools để AI agents tương tác với ứng dụng:

### Application Introspection
- **ApplicationInfo** - Thông tin về PHP và Laravel version, packages đã cài
- **GetConfig** - Đọc config values
- **ListAvailableConfigKeys** - Liệt kê các config keys
- **ListAvailableEnvVars** - Liệt kê các environment variables
- **GetAbsoluteUrl** - Lấy absolute URL của ứng dụng

### Database Tools
- **DatabaseConnections** - Xem database connections
- **DatabaseSchema** - Xem database schema
- **DatabaseQuery** - Thực thi read-only queries

### Routing & Commands
- **ListRoutes** - Liệt kê tất cả routes với middleware, controllers
- **ListArtisanCommands** - Liệt kê tất cả Artisan commands

### Debugging & Logs
- **Tinker** - Chạy PHP code trong Laravel context
- **ReadLogEntries** - Đọc log files
- **BrowserLogs** - Đọc browser console logs
- **LastError** - Xem lỗi gần nhất

### Documentation
- **SearchDocs** - Tìm kiếm trong Laravel documentation (17,000+ pieces)

## Cách hoạt động

### 1. MCP Server
Khi IDE khởi động, nó sẽ chạy command:
```bash
php artisan boost:mcp
```

Server này sẽ:
- Khởi tạo Laravel application
- Đăng ký tất cả tools từ `ToolRegistry`
- Lắng nghe requests từ IDE qua MCP protocol
- Thực thi tools và trả về kết quả

### 2. Tool Execution Flow
```
IDE/AI Agent → MCP Protocol → boost:mcp → ToolExecutor → Tool Class → Laravel App
```

### 3. Example Tool: ListArtisanCommands
```php
// vendor/laravel/boost/src/Mcp/Tools/ListArtisanCommands.php
class ListArtisanCommands extends Tool
{
    public function handle(Request $request): Response
    {
        $commands = Artisan::all();
        // ... process and return
        return Response::json($commandList);
    }
}
```

## Test Boost

### Test 1: Kiểm tra MCP server có chạy được không
```bash
php artisan boost:mcp
```
Server sẽ chạy và chờ requests từ IDE.

### Test 2: Xem các Artisan commands
AI agent có thể gọi tool `list-artisan-commands` để xem tất cả commands.

### Test 3: Database Query Tool
AI agent có thể dùng `database-query` để query database mà không cần biết cấu trúc.

### Test 4: Documentation Search
AI agent có thể dùng `search-docs` để tìm kiếm trong Laravel docs với version-specific results.

## Lợi ích

1. **Context-aware AI**: AI hiểu rõ cấu trúc ứng dụng của bạn
2. **Version-specific**: Documentation và guidelines phù hợp với version bạn đang dùng
3. **Safe operations**: Read-only tools để tránh thay đổi không mong muốn
4. **Composable guidelines**: Có thể thêm custom guidelines cho project

## Custom Guidelines

Bạn có thể thêm custom guidelines trong `.ai/guidelines/`:
```
.ai/
└── guidelines/
    ├── api-conventions.md
    ├── architecture.md
    └── testing-standards.blade.php
```

## Cập nhật

Để cập nhật guidelines lên version mới nhất:
```bash
php artisan boost:update
```

## Tài liệu tham khảo

- [Laravel Boost Documentation](https://laravel.com/docs/12.x/ai)
- [Model Context Protocol](https://modelcontextprotocol.io/)

