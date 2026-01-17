Ok, mình sẽ **tối ưu Home theo triết lý: “vào là làm được việc ngay”**, cắt tối đa thao tác, **focus 2 hành vi chính: *Check-in* và *Xem timeline***.

---

## 🎯 UX Goal (kim chỉ nam)

> **Home = 1 hành động chính + 1 dòng thời gian**

* Không menu rối
* Không decision fatigue
* Không cần học

---

## 1. Mental model người dùng (rất quan trọng)

Khi user mở app, họ đang nghĩ:

1. **“Tôi muốn check-in nhanh”**
2. **“Xem hôm nay / gần đây có gì”**

👉 **KHÔNG**:

* cấu hình
* quản lý
* thống kê chi tiết

---

## 2. Cấu trúc Home đề xuất (1 màn hình – 1 flow)

![Image](https://cdn.dribbble.com/userupload/13709810/file/original-cca8eff318f756f1ca01f6c0fae1057a.png?resize=752x\&vertical=center)

![Image](https://images.ctfassets.net/lzny33ho1g45/3V8KkxNntBr2bNDhhRLHFc/f6b25c5a82e25b1bec40bdffa129d9e0/image9.jpg)

![Image](https://platform.theverge.com/wp-content/uploads/sites/2/2025/02/Iconfactory-2.jpg?crop=0%2C0%2C100%2C100\&quality=90\&strip=all\&w=2400)

![Image](https://cdn.dribbble.com/userupload/5971903/file/original-3a1c03e62e1ce1fd976ef53b2672ccbb.png?format=webp\&resize=400x300\&vertical=center)

### 🧱 Layout tổng thể (Top → Bottom)

```
[ Greeting + Today ]
[ BIG CHECK-IN CTA ]
[ Timeline (Today / Recent) ]
```

---

## 3. Khu vực 1 – Header (cực kỳ nhẹ)

### ❌ Không nên

* avatar to
* menu phức tạp
* số liệu rối

### ✅ Nên

```
Chào Ngọc 👋
Hôm nay • Thứ Ba, 17/01
```

👉 Header chỉ để **định vị thời gian**, không phải dashboard.

---

## 4. Khu vực 2 – Check-in (trái tim của Home ❤️)

### 🎯 Nguyên tắc

* **1 nút**
* **1 hành động**
* **1 chạm**

### UI

```
[  Check-in ngay  ]
```

* Button **to, full-width**
* Màu nổi bật duy nhất trên màn hình
* Sticky (luôn nhìn thấy khi scroll nhẹ)

👉 Không hỏi nhiều thứ ở Home
👉 Click vào mới mở flow chi tiết

---

### 🚀 Bonus (rất nên cho MVP)

**Auto-context check-in**

* Nếu hôm nay chưa check-in → CTA = “Check-in hôm nay”
* Nếu đã check-in → CTA = “Check-in thêm”

---

## 5. Khu vực 3 – Timeline (xem là hiểu ngay)

![Image](https://platform.theverge.com/wp-content/uploads/sites/2/2025/02/Iconfactory-2.jpg?crop=0%2C0%2C100%2C100\&quality=90\&strip=all\&w=2400)

![Image](https://www.researchgate.net/publication/370676140/figure/fig1/AS%3A11431281390809249%401745296088909/Activity-timeline-in-app-a-Predictions-in-the-timeline-The-check-mark-indicates-they_Q320.jpg)

![Image](https://cdn.dribbble.com/userupload/39772774/file/original-9cbd5673874784bcdf24dabd923b7393.png?format=webp\&resize=400x300\&vertical=center)

### Timeline = log + cảm xúc + bằng chứng

**Item tối giản:**

```
🟢 08:45
Check-in
🙂 Cảm thấy ổn
```

Hoặc:

```
🔵 21:10
Check-in
😴 Hơi mệt
```

### Nguyên tắc timeline

* **Reverse chronological**
* Không pagination phức tạp
* Scroll là đủ

---

## 6. Tối ưu thao tác (UX friction audit)

| Hành động            | Số tap             |
| -------------------- | ------------------ |
| Mở app → check-in    | **1 tap**          |
| Xem lịch sử hôm nay  | **0 tap (scroll)** |
| Xem chi tiết 1 entry | 1 tap              |
| Edit / delete        | Long-press         |

👉 Đây là UX **rất mạnh cho retention**

---

## 7. Những thứ KHÔNG để ở Home

❌ Settings
❌ Profile
❌ Analytics
❌ Chart phức tạp

👉 Đưa hết sang **tab phụ / màn hình khác**

---

## 8. Bottom navigation (nếu buộc phải có)

```
[ Home ]  [ Timeline ]  [ Settings ]
```

* **Home = check-in**
* Timeline tab chỉ là bản full
* Settings càng xa càng tốt 😄

---

## 9. Nguyên tắc thiết kế cho MVP (đừng overdesign)

* Không filter nâng cao
* Không search
* Không tag phức tạp
* Không chart ở Home

👉 **Speed > Beauty**

---

## 10. KPI UX cần đạt (rất thực tế)

* Time-to-check-in < **3s**
* Home bounce rate < **10%**
* ≥ 70% user check-in từ Home

---

## 11. Tóm tắt 1 câu

> **Home chỉ có 2 việc: Check-in nhanh và xem mình đã làm gì. Mọi thứ khác là nhiễu.**

