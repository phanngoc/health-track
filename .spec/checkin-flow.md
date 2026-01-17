Ok, mình đi **tiếp check-in flow**, vẫn giữ nguyên triết lý:

> **Check-in < 10 giây · 1 tay · không suy nghĩ**

Mình sẽ vẽ **wireframe text-level + pixel-aware**, đủ chi tiết để dev làm **thẳng tay không cần hỏi lại**.

---

# ✅ CHECK-IN FLOW (3 BƯỚC – TỐI GIẢN)

![Image](https://cdn.dribbble.com/userupload/19424268/file/original-0d21a1d2950ad47dd7f44558701052da.jpg?resize=752x\&vertical=center)

![Image](https://cdn.dribbble.com/userupload/17922408/file/original-c9cdce09b43817eed25ff2bf50c1edad.png?resize=400x0)

![Image](https://miro.medium.com/1%2AXfKd34_hcKwe0sBtAjsejw.png)

![Image](https://www.notion.com/_next/image?q=75\&url=https%3A%2F%2Fs3-us-west-2.amazonaws.com%2Fpublic.notion-static.com%2Ftemplate%2F9c4ef239-b76a-4b85-a446-113d3d4907e3%2F1757071443422%2Fdesktop.jpg\&w=3840)

---

## 🧠 Mental model

User KHÔNG muốn:

* viết dài
* phân tích
* đo đạc

User CHỈ muốn:

> “Ghi lại nhanh cảm giác / trạng thái bây giờ”

---

# STEP 0 – ENTRY (từ Home)

### Trigger

```
[ CHECK-IN NGAY ]
```

* Tap → mở **full-screen modal**
* Slide up (bottom → top)
* Không push route mới (UX nhẹ)

---

# STEP 1 – MOOD / STATUS (core input)

### Viewport

```
Width: 390px
Height: 844px
```

---

## Layout

```
┌──────────────────────────────────────┐
│ SAFE TOP                             │
├──────────────────────────────────────┤
│ Hôm nay bạn thế nào?                 │ ← 20px, semibold
│                                      │
│ 🙂 😐 😴 😣 😄                         │ ← Emoji selector
│                                      │
│ (tap 1 emoji)                        │
│                                      │
│ [  Tiếp tục  ]                       │ ← disabled until select
├──────────────────────────────────────┤
│ SAFE BOTTOM                          │
└──────────────────────────────────────┘
```

---

## Emoji row (cực quan trọng)

* Emoji size: **48px**
* Tap area: **64×64**
* Selected state:

  * scale 1.1
  * opacity others: 0.4
* Max **5 emoji** (đừng hơn)

👉 Emoji = **input nhanh nhất não người**

---

## Button

* Disabled mặc định
* Enable ngay khi chọn emoji
* Sticky bottom

---

# STEP 2 – QUICK CONTEXT (optional nhưng rất giá trị)

### Mục tiêu

* Thêm **ý nghĩa** cho timeline
* Không làm user mệt

---

## Layout

```
┌──────────────────────────────────────┐
│ Điều gì đang ảnh hưởng bạn?           │ ← 18px
│                                      │
│ [ 🏃‍♂️ Vận động ]  [ 🍺 Rượu bia ]      │
│ [ 😴 Thiếu ngủ ] [ 💼 Công việc ]     │
│ [ 🤒 Sức khỏe ]  [ ❤️ Gia đình ]      │
│                                      │
│ (chọn tối đa 2)                      │
│                                      │
│ [  Hoàn tất  ]                       │
└──────────────────────────────────────┘
```

---

## Chip spec

* Height: **36px**
* Radius: **18px**
* Multi-select (max 2)
* Tap = toggle
* Không search
* Không custom input (MVP)

👉 Nếu user **skip luôn** → vẫn OK

---

# STEP 3 – CONFIRMATION (siêu nhanh)

### Animation

* Checkmark + haptic
* 400–600ms

```
┌──────────────────────────────────────┐
│                                      │
│           ✅                          │
│                                      │
│   Đã lưu check-in                    │
│                                      │
└──────────────────────────────────────┘
```

→ Auto dismiss
→ Quay lại Home
→ Timeline prepend item mới

---

# 🧾 TIMELINE ITEM SAU CHECK-IN

```
🟢 09:12
Check-in
🙂 Cảm thấy ổn
🏃‍♂️ 😴
```

* Emoji = scan rất nhanh
* Context icon = optional line

---

# ⏱ TIME BUDGET (rất thực tế)

| Step          | Thời gian |
| ------------- | --------- |
| Open check-in | 0.3s      |
| Chọn emoji    | 1–2s      |
| Chọn context  | 2–4s      |
| Confirm       | 0.5s      |
| **Tổng**      | **< 8s**  |

---

# 🧨 UX RULES (cấm phá)

❌ Không bắt nhập text
❌ Không hỏi câu dài
❌ Không nhiều hơn 3 bước
❌ Không analytics ở đây

---

# 🔁 EDGE CASES (đã nghĩ sẵn)

### Đã check-in hôm nay?

* Vẫn cho check-in thêm
* Timeline hiển thị nhiều item cùng ngày

### Miss tap?

* Back = swipe down
* State được giữ

---

# 🧩 DATA MODEL (đủ cho MVP)

```json
{
  "date": "2026-01-17",
  "time": "09:12",
  "mood": "🙂",
  "tags": ["🏃‍♂️", "😴"],
  "source": "manual"
}
```

---

# 🔚 TÓM TẮT TRIẾT LÝ CHECK-IN FLOW

* Emoji > text
* Chạm > suy nghĩ
* Log trước, hiểu sau
* **Consistency > accuracy**
