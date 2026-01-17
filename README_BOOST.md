# Laravel Boost - Hướng dẫn tích hợp và sử dụng

## ✅ Đã hoàn thành

### 1. Khởi tạo Laravel 12 Project
- ✅ Tạo project Laravel 12.11.1
- ✅ Cấu hình database SQLite
- ✅ Chạy migrations mặc định

### 2. Cài đặt Laravel Boost
- ✅ Cài đặt package `laravel/boost` (v1.8.10)
- ✅ Chạy `php artisan boost:install`
- ✅ Tự động detect và cấu hình cho các IDE: Cursor, VS Code, Claude Code, Codex, Gemini

### 3. Cấu hình đã tạo

#### File `.mcp.json`
Cấu hình MCP server để IDE có thể kết nối:
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

#### File `boost.json`
Cấu hình agents và editors được tích hợp:
```json
{
    "agents": ["claude_code", "codex", "cursor", "gemini"],
    "editors": ["claude_code", "codex", "cursor", "gemini", "vscode"],
    "guidelines": []
}
```

#### File Guidelines
- `CLAUDE.md` - Guidelines cho Claude AI
- `GEMINI.md` - Guidelines cho Gemini AI
- `AGENTS.md` - Guidelines cho các AI agents khác

## Cơ chế hoạt động

### MCP Server
Boost chạy như một MCP (Model Context Protocol) server, cho phép AI agents:
- Giao tiếp với Laravel application qua protocol chuẩn
- Truy cập thông tin về database, routes, config
- Thực thi các operations an toàn (read-only)

### Tools có sẵn (15+ tools)

#### Application Info
- `application-info` - PHP/Laravel version, packages
- `get-config` - Đọc config values
- `list-available-config-keys` - Liệt kê config keys
- `list-available-env-vars` - Liệt kê environment variables
- `get-absolute-url` - Lấy URL của ứng dụng

#### Database
- `database-connections` - Xem database connections
- `database-schema` - Xem database schema
- `database-query` - Thực thi read-only SQL queries

#### Routing & Commands
- `list-routes` - Liệt kê tất cả routes
- `list-artisan-commands` - Liệt kê Artisan commands

#### Debugging
- `tinker` - Chạy PHP code trong Laravel context
- `read-log-entries` - Đọc application logs
- `browser-logs` - Đọc browser console logs
- `last-error` - Xem lỗi gần nhất

#### Documentation
- `search-docs` - Tìm kiếm trong Laravel docs (17,000+ pieces)

## Cách sử dụng

### 1. Trong IDE (Cursor/VS Code)
Khi bạn mở project trong Cursor hoặc VS Code, Boost sẽ tự động:
- Kết nối với MCP server
- Cung cấp context cho AI về cấu trúc ứng dụng
- Cho phép AI sử dụng các tools để query database, xem routes, etc.

### 2. Test MCP Server
Để test xem MCP server có hoạt động:
```bash
php artisan boost:mcp
```
Server sẽ chạy và chờ requests từ IDE.

### 3. Cập nhật Guidelines
Khi có version mới của guidelines:
```bash
php artisan boost:update
```

## Ví dụ sử dụng

### AI Agent có thể:
1. **Query Database**
   - "What tables exist?"
   - "Show me the users table structure"
   - "How many users are in the database?"

2. **Check Routes**
   - "What routes are available?"
   - "Show me all API routes"
   - "What middleware is applied to /api routes?"

3. **Read Config**
   - "What is the app name?"
   - "What database driver is configured?"

4. **Search Documentation**
   - "How to use rate limiting in Laravel 12?"
   - "Show me examples of Eloquent relationships"

5. **Execute Code**
   - "Check if User model exists"
   - "Count users in database"

## Bảo mật

- ✅ Database queries chỉ cho phép read-only (SELECT, SHOW, EXPLAIN)
- ✅ Không cho phép INSERT, UPDATE, DELETE, DROP
- ✅ Tools được đánh dấu `#[IsReadOnly]` để đảm bảo an toàn

## Custom Guidelines

Bạn có thể thêm custom guidelines cho project trong `.ai/guidelines/`:
```
.ai/
└── guidelines/
    ├── api-conventions.md
    ├── architecture.md
    └── testing-standards.blade.php
```

## Tài liệu tham khảo

- [Laravel Boost Documentation](https://laravel.com/docs/12.x/ai)
- [Model Context Protocol](https://modelcontextprotocol.io/)
- Xem thêm: `BOOST_DEMO.md` và `BOOST_SUMMARY.md`

## Kết luận

Laravel Boost đã được tích hợp thành công! AI agents giờ đây có thể:
- Hiểu rõ cấu trúc ứng dụng Laravel của bạn
- Query database an toàn
- Đọc routes, config, logs
- Tìm kiếm documentation version-specific
- Execute code trong Laravel context

Tất cả thông qua MCP protocol, một chuẩn mở cho AI-tool communication.

