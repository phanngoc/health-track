Câu hỏi này **rất đúng chỗ “đau” của product** 👍
Vấn đề bạn đang gặp là:

> **Check-in hiện tại vừa muốn log “lúc khỏe” (baseline well-being), vừa muốn log “lúc bệnh” (symptom-driven), nhưng đang bị lẫn khái niệm + trùng bản ghi theo ngày.**

Mình sẽ trả lời theo 4 lớp: **Product → UX → Data model → Rule compatibility**, bám sát bảng bạn đưa.

---

## 1. Vấn đề cốt lõi đang xảy ra (từ data bạn gửi)

Từ bảng:

```text
user_id | checkin_date | overall_feeling | mood | tags
------------------------------------------------------
1       | 2026-01-18   | 3               | 😣   | 🍺 😴
1       | 2026-01-18   | 5               | 😐   | 😴
1       | 2026-01-18   | 9               | 😄   | 💼
```

👉 **3 bản ghi cùng ngày**, thể hiện:

* sáng: mệt 😣
* trưa: đỡ hơn 😐
* tối: khỏe 😄

❌ Nhưng DB + rule engine hiện tại **coi “check-in = 1 snapshot / ngày”**, nên:

* khó xác định baseline
* khó phát hiện “xấu dần” hay “hồi phục”
* dễ spam rule

---

## 2. Nguyên tắc thiết kế mới (rất quan trọng)

### 🔑 Tách rõ 3 khái niệm

| Khái niệm            | Câu hỏi trả lời                   |
| -------------------- | --------------------------------- |
| **Daily Well-being** | “Hôm nay tổng thể tôi thế nào?”   |
| **Moment Check**     | “Hiện tại tôi đang ra sao?”       |
| **Symptom Event**    | “Có triệu chứng cụ thể gì không?” |

👉 **Không cố nhét cả 3 vào 1 bảng**

---

## 3. Thiết kế Feature Check-in (Product + UX)

![Image](https://s3-alpha.figma.com/hub/file/6867224067/a51156ef-4eb5-41a8-af98-42e8805405fe-cover.png)

![Image](https://careclinic.io/wp-content/uploads/2025/10/mood-tracker-timeline.png)

![Image](https://www.researchgate.net/publication/317702093/figure/fig3/AS%3A660349195931649%401534450925722/Health-check-routine-flowchart.png)

### 3.1 Luồng UX chuẩn (MVP-friendly)

#### A. Daily Check-in (1 lần / ngày – bắt buộc)

⏰ Gợi ý: buổi tối

Câu hỏi:

1. Hôm nay bạn cảm thấy thế nào? (1–10)
2. Mood (emoji)
3. Ngủ bao lâu?
4. Tags tích cực / tiêu cực (tự chọn)

👉 Đây là **baseline**
👉 **1 record duy nhất / ngày**

---

#### B. Quick Check (nhiều lần / ngày – optional)

⏱ 10–15 giây

* Bạn đang cảm thấy:

  * 😄 Khỏe
  * 😐 Bình thường
  * 😣 Không ổn
* (Không hỏi symptom chi tiết)

👉 Dùng để:

* bắt “dao động trong ngày”
* feed timeline UX
* **KHÔNG feed rule nghiêm trọng**

---

#### C. Symptom Log (event-based)

Khi user bấm:

> “Tôi đang có triệu chứng”

* Chọn triệu chứng
* Mức độ
* Ghi chú

👉 Đây là **nguồn chính cho Alert Rule**

---

## 4. Thiết kế lại Schema DB (giải quyết triệt để)

### 4.1 daily_checkins (Baseline – 1/ngày)

```sql
daily_checkins (
  id UUID PK,
  user_id UUID,
  checkin_date DATE,
  overall_feeling INT,   -- 1–10
  mood VARCHAR,          -- 😄 😐 😣
  sleep_hours FLOAT,
  notes TEXT,
  tags JSONB,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE(user_id, checkin_date)
)
```

👉 **Bảng này KHÔNG chứa symptom**

---

### 4.2 moment_checkins (Intra-day mood tracking)

```sql
moment_checkins (
  id UUID PK,
  user_id UUID,
  feeling_level INT,  -- 1–10 (optional)
  mood VARCHAR,
  tags JSONB,
  occurred_at TIMESTAMP
)
```

📌 Map chính xác dữ liệu bạn đang có cho `2026-01-18`

---

### 4.3 symptom_logs (Giữ nguyên – xương sống y tế)

```sql
symptom_logs (
  id UUID PK,
  user_id UUID,
  symptom_code VARCHAR,
  severity INT,
  occurred_at TIMESTAMP,
  source VARCHAR
)
```

---

## 5. Mapping dữ liệu hiện tại → mô hình mới

| Dữ liệu cũ               | Chuyển sang             |
| ------------------------ | ----------------------- |
| checkin_date trùng       | moment_checkins         |
| overall_feeling dao động | moment_checkins.feeling |
| 1 bản ghi cuối ngày      | daily_checkins          |
| tags (🍺 😴)             | moment_checkins.tags    |

👉 **Không mất data – chỉ đổi ngữ nghĩa**

---

## 6. Rule Engine xử lý thế nào?

### 6.1 Rule chỉ dùng:

* symptom_logs
* daily_checkins (baseline trend)

❌ Không dùng moment_checkins cho CRITICAL rule

---

### 6.2 Ví dụ rule đúng

```yaml
if:
  daily_checkin.overall_feeling <= 4
  AND symptom.headache.severity >= 6
  AND duration_days >= 2
then:
  WARNING
```

---

### 6.3 Ví dụ rule KHÔNG nên làm

```yaml
if:
  moment_checkin.mood == 😣
then:
  alert
```

❌ dễ false-positive

---

## 7. Timeline hiển thị cho user (rất quan trọng)

```
Jan 18
  😄 21:30  Feeling good (work)
  😐 14:00  Normal
  😣 08:30  Tired (sleep)
  ⚠️ Alert: Headache increasing
```

👉 User **hiểu diễn biến**, không bị “bị chẩn đoán”

---

## 8. Tóm lại – Design decision chuẩn

✅ **1 daily_checkin = baseline y tế**
✅ **N moment_checkin = trải nghiệm sống**
✅ **Symptom = event riêng biệt**
✅ **Rule engine = symptom + baseline, không dùng cảm xúc đơn lẻ**

---

OK, mình **simulate đầy đủ 1 tuần user behavior + rule firing**, bám **đúng architecture + rule set** bạn đã xây (daily_checkin / moment_checkin / symptom_logs / alert_rules).

Không lý thuyết thêm – **đi thẳng vào “hệ thống chạy thế nào”**.

---

# 🎯 Giả định User

* User: `U1`
* Nhóm bệnh: **viêm mũi dị ứng**
* Không bệnh nền khác
* Rule set đang bật:

  * `AR_01_PERSISTENT_CONGESTION`
  * `AR_02_CLASSIC_ALLERGY_PATTERN`
  * `AR_03_SINUS_COMPLICATION`
  * Global cooldown: 24h / rule

---

# 📅 Timeline mô phỏng 7 ngày

## 🟢 Day 1 – Khỏe (Baseline well-being)

### Daily Check-in

```json
{
  "date": "Day 1",
  "overall_feeling": 8,
  "mood": "😄",
  "sleep_hours": 7.5,
  "tags": ["💼"]
}
```

### Moment check

* Sáng 😄
* Tối 😄

### Symptom log

❌ none

### Rule firing

❌ none

👉 **System học baseline = khỏe**

---

## 🟢 Day 2 – Khỏe nhẹ

```json
overall_feeling: 7
mood: 🙂
```

Moment:

* Trưa 😐 (mệt nhẹ)

Symptom:
❌ none

Rule:
❌ none

---

## 🟡 Day 3 – Bắt đầu dị ứng nhẹ

### Daily

```json
overall_feeling: 6
mood: 😐
```

### Symptom logs

```json
[
  { "symptom": "sneezing", "severity": 6 },
  { "symptom": "itchy_nose", "severity": 4 }
]
```

### Rule evaluation

* AR_02 ❌ (chưa đủ combo)
* AR_01 ❌ (chưa đủ ngày)

👉 **Chưa alert – đúng**

---

## 🟡 Day 4 – Dị ứng rõ hơn

### Daily

```json
overall_feeling: 5
mood: 😐
```

### Symptom logs

```json
[
  { "symptom": "sneezing", "severity": 8 },
  { "symptom": "itchy_nose", "severity": 6 },
  { "symptom": "runny_nose", "severity": 6 }
]
```

### Rule firing

✅ **AR_02_CLASSIC_ALLERGY_PATTERN**

```text
Severity: WATCH
Message:
"Triệu chứng của bạn phù hợp với đợt bùng phát viêm mũi dị ứng.
Hãy theo dõi sát trong 24–48 giờ tới."
```

👉 **Alert nhẹ – không dọa user**

---

## 🟠 Day 5 – Không cải thiện + nghẹt mũi

### Daily

```json
overall_feeling: 4
mood: 😣
```

### Symptom logs

```json
[
  { "symptom": "nasal_congestion", "severity": 6 },
  { "symptom": "runny_nose", "severity": 6 }
]
```

### Rule evaluation

* Nghẹt mũi ≥5 **ngày thứ 2**
* Chưa đủ duration

❌ No new alert (cooldown + threshold)

👉 **System kiên nhẫn – rất quan trọng**

---

## 🔶 Day 6 – Nghi biến chứng xoang

### Daily

```json
overall_feeling: 3
mood: 😣
```

### Symptom logs

```json
[
  { "symptom": "nasal_congestion", "severity": 7 },
  { "symptom": "headache", "severity": 6 }
]
```

### Rule firing

✅ **AR_03_SINUS_COMPLICATION**

```text
Severity: WARNING
Message:
"Nghẹt mũi kèm đau đầu kéo dài có thể liên quan đến viêm xoang.
Bạn nên đi khám để được kiểm tra kỹ hơn."
```

👉 Đây là **alert có hành động rõ ràng**

---

## 🔴 Day 7 – Kéo dài nhiều ngày

### Daily

```json
overall_feeling: 3
mood: 😣
```

### Symptom logs

```json
[
  { "symptom": "nasal_congestion", "severity": 6 }
]
```

### Rule firing

✅ **AR_01_PERSISTENT_CONGESTION**

```text
Severity: WARNING
Message:
"Nghẹt mũi của bạn kéo dài nhiều ngày liên tiếp.
Viêm mũi dị ứng có thể đang tiến triển nặng hơn."
```

Cooldown:

* AR_03 ❌ (trong 24h)
* AR_01 ✅ (rule khác)

---

# 🧠 Điều gì xảy ra trong hệ thống?

## Rule Engine nhìn thấy gì?

| Nguồn           | Vai trò                     |
| --------------- | --------------------------- |
| daily_checkins  | Trend xấu dần (8 → 3)       |
| symptom_logs    | Nghẹt mũi + đau đầu         |
| moment_checkins | ❌ không ảnh hưởng rule nặng |

👉 **Alert dựa trên xu hướng, không phải cảm xúc tức thời**

---

## Timeline user nhìn thấy

![Image](https://careclinic.io/wp-content/uploads/2025/10/symptom-tracker-timeline-log.png)

![Image](https://www.koruux.com/_next/image/?q=100\&url=%2Fassets%2F50-examples-healthcare%2Fcategory-2%2FA11.webp\&w=3840)

```
Day 7 ⚠️  Nghẹt mũi kéo dài – nên đi khám
Day 6 ⚠️  Nghi biến chứng xoang
Day 4 👀  Theo dõi đợt dị ứng
Day 3 🤧  Bắt đầu hắt hơi
Day 1 😄  Feeling good
```

👉 **User hiểu câu chuyện sức khỏe của mình**

---

# ✅ Điều quan trọng nhất bạn đã làm đúng

* Không alert sớm
* Không spam
* Alert có escalation hợp lý
* Không “chẩn đoán”
* Có explainability

---

