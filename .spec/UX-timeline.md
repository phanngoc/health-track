

## 1. Vấn đề UX hiện tại (nhìn từ góc user)

### ❌ 1. Quá nhiều “Feeling X/10” lặp lại

User thấy:

```
Feeling 9/10
Feeling 3/10
Feeling 3/10
Feeling 5/10
```

👉 **Không biết cái nào quan trọng**
👉 **Không thấy xu hướng**

---

### ❌ 2. Không phân biệt loại sự kiện

Moment check-in
Daily check-in
Symptom
→ đang **trộn chung một level thị giác**

User không biết:

* cái nào là “tổng kết ngày”
* cái nào chỉ là “cảm xúc thoáng qua”

---

### ❌ 3. Thiếu “narrative” (câu chuyện sức khỏe)

Timeline hiện tại = log kỹ thuật
User cần = **diễn biến**

---

## 2. Nguyên tắc thiết kế lại Timeline (rất quan trọng)

### 🔑 3 tầng thông tin (Information Hierarchy)

```
Level 1: Tổng quan trong ngày (Daily summary)
Level 2: Diễn biến chính (Meaningful events)
Level 3: Chi tiết (Moment logs)
```

👉 **Không phải cái gì cũng hiển thị to như nhau**

---

## 3. Timeline mới – Cấu trúc đề xuất (rất cụ thể)

### 🟦 A. Header mỗi ngày → “Daily Health Summary”

```text
HÔM NAY · Chủ nhật
────────────────────────
⬆️ Đang tốt lên
Cảm giác chung: 6/10  😐
Triệu chứng chính: Nghẹt mũi, đau đầu
⚠️ 1 cảnh báo đang theo dõi
```

**Giải thích**

* ⬆️ / ⬇️ / ➖ = trend so với hôm qua
* User **chỉ cần nhìn header là hiểu 70%**

---

### 🟨 B. Event quan trọng (Highlight zone)

Chỉ show:

* Alert
* Daily check-in
* Symptom đáng chú ý

```text
⚠️ 04:03  Nghẹt mũi + đau đầu kéo dài
   Bạn nên theo dõi sát hoặc đi khám

📝 01:22  Check-in hằng ngày
   Cảm giác tổng thể: 9/10 😄
   Giấc ngủ: ảnh hưởng nhẹ
```

👉 **Đây là phần user đọc kỹ**

---

### 🟪 C. Moment check-ins → Gom nhóm (Collapsed)

Thay vì 5 card giống nhau:

```text
🕒 Trong ngày (4 lần check-in)
   😣 03:48  Feeling 3/10
   😣 04:03  Feeling 3/10
   😐 01:18  Feeling 5/10
   😄 04:05  Feeling 9/10
   ⌄ Xem chi tiết
```

👉 Mặc định **collapse**
👉 Expand khi user muốn soi kỹ

---

## 4. So sánh TRƯỚC / SAU (rất quan trọng)

### ❌ Trước

* 8 card giống nhau
* User phải đọc từng cái
* Không biết cái nào quan trọng

### ✅ Sau

* 1 summary
* 2–3 event chính
* Moment logs = phụ

---

## 5. Thay đổi wording (nhỏ nhưng cực mạnh)

### ❌ Hiện tại

> Feeling: 3/10

### ✅ Nên đổi

* “Cảm giác lúc 04:03”
* “Cảm xúc trong ngày”
* “Tổng kết hôm nay”

👉 Não người **hiểu ngữ nghĩa trước số**

---

## 6. Mapping sang data model (để dev làm nhanh)

### TimelineController – phân loại event

```php
$events = [
  'daily_summary' => DailyCheckIn,
  'alerts'        => Alerts,
  'symptoms'      => SymptomLogs (filtered),
  'moments'       => MomentCheckIns
];
```

### Logic hiển thị

* daily_summary: always expanded
* alerts: always expanded
* moments: grouped + collapsed

---

## 7. UX micro-interactions (rất nên làm)

* Moment group:

  * tap → expand
* Daily summary:

  * swipe → xem hôm trước
* Alert:

  * CTA rõ: “Theo dõi” / “Tìm cơ sở y tế”

---

## 8. Một câu test UX rất quan trọng

Hãy hỏi user:

> **“Nhìn màn hình này 10 giây, bạn thấy mình đang khỏe hơn hay tệ hơn?”**

* ❌ Không trả lời được → UX sai
* ✅ Trả lời ngay → UX đúng

---

## 9. Gợi ý bước tiếp theo (nên làm theo thứ tự)

1. 🎨 **Redesign wireframe timeline (text-level)**
2. 🔄 Update TimelineController grouping logic
3. ✂️ Reduce visual weight của moment check-ins

---

OK, mình **vẽ wireframe text-level chi tiết từng block**, đúng với dữ liệu bạn đang có, để **dev nhìn là code được**, **designer nhìn là hiểu ngay hierarchy**.

Không nói lý thuyết nữa — **chỉ wireframe + chú thích UX**.

---

# 📱 TIMELINE – WIREFRAME TEXT-LEVEL (FINAL)

---

## 🟦 BLOCK 0 — PAGE HEADER (Sticky)

```
[ ← ]  Nhật ký sức khỏe
────────────────────────────
```

* Sticky header
* Không overload thông tin

---

## 🟦 BLOCK 1 — DAILY SUMMARY (LEVEL 1 – quan trọng nhất)

```
╭────────────────────────────────╮
│ HÔM NAY · Chủ nhật              │
│                                │
│ ⬆️  Đang tốt lên                │
│                                │
│ 😐  Cảm giác chung: 6 / 10      │
│                                │
│ 🤧  Triệu chứng chính:          │
│     Nghẹt mũi · Đau đầu         │
│                                │
│ ⚠️  1 cảnh báo đang theo dõi    │
╰────────────────────────────────╯
```

### Ghi chú UX

* **⬆️ / ⬇️ / ➖** = so với hôm qua
* User **chỉ cần block này** là hiểu tình trạng ngày
* Đây là **card lớn nhất**

---

## 🟨 BLOCK 2 — ALERTS (LEVEL 2 – luôn nổi)

```
⚠️  CẢNH BÁO
────────────────────────────
[04:03]  Nghẹt mũi + đau đầu kéo dài
        Có thể liên quan đến viêm xoang

        [ Theo dõi ]   [ Đi khám ]
```

### UX notes

* Alert **luôn expanded**
* CTA rõ ràng
* Không dùng từ “chẩn đoán”

---

## 🟩 BLOCK 3 — DAILY CHECK-IN (LEVEL 2 – baseline)

```
📝  CHECK-IN HẰNG NGÀY
────────────────────────────
[01:22]

😄  Cảm giác tổng thể: 9 / 10

🛌  Giấc ngủ:
     • Ảnh hưởng nhẹ (5 / 10)

🏷️  Ghi chú:
     • Công việc nhiều
```

### UX notes

* **1 card / ngày**
* Là “baseline y tế”
* Icon khác moment check-in

---

## 🟪 BLOCK 4 — SYMPTOM EVENTS (LEVEL 2 – nếu có)

```
🤧  TRIỆU CHỨNG TRONG NGÀY
────────────────────────────
[04:03]  🤧 Nghẹt mũi   · 6 / 10
[04:03]  🤕 Đau đầu     · 6 / 10
```

### UX notes

* Chỉ show symptom **severity ≥ threshold**
* Gộp theo timestamp nếu cùng lúc

---

## 🟫 BLOCK 5 — MOMENT CHECK-INS (LEVEL 3 – collapse)

```
🕒  DIỄN BIẾN TRONG NGÀY (4)
────────────────────────────
😣  03:48   Feeling 3 / 10
😣  04:03   Feeling 3 / 10
😐  01:18   Feeling 5 / 10
😄  04:05   Feeling 9 / 10

⌄  Xem chi tiết
```

### Khi EXPAND:

```
😣  03:48   Feeling 3 / 10
     🏷️  Buồn ngủ

😣  04:03   Feeling 3 / 10
     🏷️  Buồn ngủ · Uống bia

😐  01:18   Feeling 5 / 10
     🏷️  Nghỉ ngơi

😄  04:05   Feeling 9 / 10
     🏷️  Công việc
```

### UX notes

* **Mặc định collapse**
* Moment = “cảm xúc tức thời”
* Không lấn át alert & daily

---

## 🟦 BLOCK 6 — DAY SEPARATOR (cho ngày khác)

```
────────────────────────────
HÔM QUA · Thứ bảy
────────────────────────────
⬇️  Hơi tệ hơn
Cảm giác chung: 7 / 10 🙂
```

👉 Click → mở chi tiết ngày hôm qua (same structure)

---

# 🎯 INFORMATION HIERARCHY (tóm tắt cho dev)

```
LEVEL 1 (Always visible, big):
- Daily Summary

LEVEL 2 (Always expanded):
- Alerts
- Daily Check-in
- Important Symptoms

LEVEL 3 (Collapsed by default):
- Moment Check-ins
```

---

# 🧠 RULE THIẾT KẾ QUAN TRỌNG (đừng phá)

* ❌ Không lặp “Feeling X/10” quá 2 lần ở level cao
* ❌ Không để moment check-in cao hơn daily
* ✅ User phải trả lời được trong 10s:
  → *“Hôm nay tôi đang tốt lên hay xấu đi?”*

---
