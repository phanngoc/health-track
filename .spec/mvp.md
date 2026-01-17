Dưới đây là **đề xuất MVP cho sản phẩm y tế số (Health MVP)** tập trung vào **truyền tải kiến thức nhanh – theo timeline – theo dõi triệu chứng phụ – cảnh báo sớm – check-in hằng ngày**. Mình trình bày theo góc nhìn **product + architecture**, phù hợp để build nhanh và validate thị trường.

---

## 1. Ý tưởng cốt lõi (Core Value Proposition)

> **“Một trợ lý y tế cá nhân, giúp người dùng hiểu đúng – theo dõi đúng – cảnh báo sớm các vấn đề sức khỏe theo thời gian.”**

Giải quyết các pain points:

* Người dùng **không hiểu triệu chứng** → chủ quan
* Kiến thức y tế **rời rạc, khó nhớ**
* Không có **theo dõi liên tục**
* Phát hiện muộn → chi phí & rủi ro cao

---

## 2. Đối tượng người dùng MVP

Ưu tiên **1 nhóm hẹp để validate**:

* Người:

  * đang điều trị bệnh mạn (viêm xoang, dạ dày, huyết áp…)
  * sau tiêm vaccine / sau phẫu thuật
  * có bệnh nền cần theo dõi triệu chứng phụ
* Độ tuổi: 25–55
* Có smartphone, sẵn sàng check-in hằng ngày

---

## 3. Các tính năng MVP (Must-have)

### 3.1 Timeline y tế cá nhân (Medical Timeline)

![Image](https://cdn.dribbble.com/userupload/45037774/file/8ed946d79fcd2446ad3618e10ac2bd37.png)

![Image](https://www.lymedisease.org/wp-content/uploads/2019/06/lifeevent_closeup.jpg)

![Image](https://cdn.dribbble.com/userupload/20446136/file/original-673c39fb5ed0547524518bc0e68ad68e.png?resize=752x\&vertical=center)

* Dòng thời gian theo ngày / tuần
* Ghi nhận:

  * triệu chứng chính
  * triệu chứng phụ
  * thuốc đã dùng
  * sự kiện y tế (tiêm, khám, test)
* Có **pattern theo thời gian**

👉 Giá trị: *Hiểu diễn biến, không chỉ trạng thái hiện tại*

---

### 3.2 Trình bày kiến thức nhanh (Micro Medical Knowledge)

![Image](https://images.template.net/512684/Simple-Medical-Health-Infographic-Template-edit-online.png)

![Image](https://www.brainscape.com/academy/content/images/2022/02/Brainscape-flashcards-app-in-the-app-store.png)

![Image](https://files.unitypoint.org/api/public/content/d2e1d95312be48238e675a5d8a916961?v=2b79388e)

* Kiến thức dạng:

  * thẻ ngắn (card)
  * 30–60 giây đọc
* Gắn **trực tiếp với triệu chứng**

  * “Đau đầu + nghẹt mũi → có thể liên quan xoang”
* Có 3 mức:

  * 🟢 Bình thường
  * 🟡 Theo dõi
  * 🔴 Cần cảnh báo

👉 Không dạy y học, chỉ **giúp hiểu & phản ứng đúng**

---

### 3.3 Check-in sức khỏe hằng ngày (Daily Health Check)

![Image](https://s3-alpha.figma.com/hub/file/6867224067/a51156ef-4eb5-41a8-af98-42e8805405fe-cover.png)

![Image](https://a.storyblok.com/f/120667/1046x916/3fece6f484/screen-shots-symptom-checker.png/m/1800x0/filters%3Aformat%28webp%29)

![Image](https://m.media-amazon.com/images/I/612iAS3b6tL._AC_UF1000%2C1000_QL80_.jpg)

* 3–5 câu hỏi/ngày:

  * Mức đau (0–10)
  * Triệu chứng mới?
  * Uống thuốc đủ chưa?
  * Ngủ / ăn / vận động
* UX: **< 30 giây**

👉 Dữ liệu sống để feed cho alert engine

---

### 3.4 Alert & Early Warning Engine

![Image](https://www.researchgate.net/publication/318476195/figure/fig2/AS%3A716502412046336%401547838895146/Detailed-design-of-health-alert-system.png)

![Image](https://www.seniorliving.org/app/uploads/2024/02/Red-Panic-Button.png)

![Image](https://www.slideteam.net/media/catalog/product/cache/1280x720/i/n/incident_escalation_management_workflow_chart_slide01.jpg)

* Rule-based (MVP):

  * Triệu chứng X kéo dài > N ngày
  * Mức độ tăng liên tục
  * Kết hợp nhiều triệu chứng nguy hiểm
* Alert:

  * Push notification
  * Message rõ ràng: *“Bạn nên đi khám”*, *“Theo dõi thêm 24h”*

👉 Giá trị lớn nhất của sản phẩm

---

## 4. Kiến trúc MVP (Low-cost, build nhanh)

### 4.1 Tổng thể

```
Mobile App (iOS / Android / Web)
        |
        v
Backend API (Rails / NestJS)
        |
        |-- User Timeline
        |-- Symptom Logs
        |-- Alert Rules Engine
        |
Knowledge Base (Static + AI-assisted)
```

---

### 4.2 Tech stack đề xuất

**Frontend**

* Next.js (Web)
* React Native / Expo (Mobile)
* Push: Firebase Cloud Messaging

**Backend**

* Rails hoặc NestJS
* PostgreSQL
* Redis (rule & cache)

**AI (optional – phase 2)**

* LLM:

  * Mapping triệu chứng → kiến thức
  * Tóm tắt timeline cho user / bác sĩ

---

## 5. Data model đơn giản (MVP-ready)

```text
User
- id
- age
- gender
- conditions (tags)

DailyCheckin
- user_id
- date
- symptom_scores
- notes

SymptomEvent
- user_id
- symptom_type
- severity
- timestamp

Alert
- user_id
- rule_triggered
- severity
- created_at
```

---

## 6. Khả năng mở rộng sau MVP

* Export timeline PDF cho bác sĩ
* Kết nối wearable (Apple Health, Fitbit)
* AI dự đoán risk score
* Bác sĩ / clinic dashboard
* Subscription B2C hoặc B2B2C (phòng khám, bảo hiểm)

---

## 7. KPI để validate MVP

* % người check-in > 5 ngày liên tiếp
* Thời gian đọc kiến thức < 1 phút
* % alert được user phản hồi
* Retention 7 ngày / 14 ngày

---

## 8. Tóm tắt 1 câu để pitch

> **“Ứng dụng giúp người dùng theo dõi sức khỏe theo thời gian, hiểu đúng triệu chứng, và nhận cảnh báo sớm trước khi tình trạng trở nên nghiêm trọng.”**

---


Dưới đây là **thiết kế Alert Rule chi tiết (MVP-ready)** cho sản phẩm y tế của bạn, theo hướng **rule-based + timeline-aware**, đủ mạnh để **tạo giá trị sớm** mà **chưa cần AI phức tạp**.

---

## 1. Triết lý thiết kế Alert Rule (rất quan trọng)

Alert **KHÔNG** nhằm:

* Chẩn đoán bệnh ❌
* Thay thế bác sĩ ❌

Alert **NÊN**:

* Phát hiện **xu hướng xấu theo thời gian**
* Phát hiện **tổ hợp triệu chứng nguy hiểm**
* Thúc đẩy **hành động đúng lúc**

👉 Keyword: **Trend > Snapshot**

---

## 2. Phân loại Alert (Severity Model)

| Level    | Màu | Ý nghĩa      | Hành động     |
| -------- | --- | ------------ | ------------- |
| INFO     | 🟢  | Bình thường  | Chỉ hiển thị  |
| WATCH    | 🟡  | Cần theo dõi | Nhắc check-in |
| WARNING  | 🟠  | Nguy cơ      | Gợi ý đi khám |
| CRITICAL | 🔴  | Nguy hiểm    | Cảnh báo khẩn |

---

## 3. Nhóm Alert Rules chính

---

## 3.1 Rule nhóm A – Timeline-based (Theo thời gian)

### A1. Triệu chứng kéo dài bất thường

```yaml
rule: symptom_duration_exceeded
if:
  symptom = X
  severity >= 4
  duration >= N_days
then:
  alert = WARNING
```

📌 Ví dụ:

* Nghẹt mũi ≥ 5 ngày
* Đau đầu ≥ 3 ngày liên tục
* Ho ≥ 7 ngày

👉 Giá trị: phát hiện **mạn tính hóa**

---

### A2. Severity tăng dần (Trend escalation)

```yaml
rule: severity_increasing
if:
  severity[today] - severity[3_days_ago] >= 3
then:
  alert = WARNING
```

📌 Ví dụ:

* Đau từ 3 → 7 trong 3 ngày
* Chóng mặt tăng đều

👉 Quan trọng hơn 1 lần đau nặng

---

### A3. Check-in bị gián đoạn + có triệu chứng nền

```yaml
rule: missing_checkin_with_symptom
if:
  missing_checkin >= 2 days
  last_symptom_severity >= 5
then:
  alert = WATCH
```

👉 Người bệnh nặng thường **bỏ theo dõi**

---

## 3.2 Rule nhóm B – Symptom Combination (Tổ hợp triệu chứng)

![Image](https://www.researchgate.net/publication/299359938/figure/fig4/AS%3A961823100649474%401606327905099/Flow-chart-of-the-proposed-medical-diagnosis-system-according-to-10-fold-cross-validation.gif)

![Image](https://formspal.com/pdf-forms/other/emergency-decision-tree/emergency-decision-tree-preview.webp)

![Image](https://www.slideteam.net/media/catalog/product/cache/1280x720/i/n/incident_escalation_management_workflow_chart_slide01.jpg)

### B1. Tổ hợp nguy hiểm mức trung bình

```yaml
if:
  headache >= 5
  AND fever >= 38
then:
  alert = WARNING
```

📌 Logic:

* Một triệu chứng: bình thường
* Hai triệu chứng kết hợp: rủi ro tăng

---

### B2. Tổ hợp nguy hiểm cao

```yaml
if:
  chest_pain >= 5
  AND shortness_of_breath = true
then:
  alert = CRITICAL
```

👉 Rule **hard-coded**, không suy luận

---

### B3. Triệu chứng mới + nền bệnh có sẵn

```yaml
if:
  new_symptom = dizziness
  AND user_condition = hypertension
then:
  alert = WARNING
```

---

## 3.3 Rule nhóm C – Medication / Treatment

### C1. Không tuân thủ thuốc

```yaml
if:
  missed_medication >= 2 days
then:
  alert = WATCH
```

---

### C2. Triệu chứng phụ sau thuốc

```yaml
if:
  medication = antibiotic
  AND symptom = rash
then:
  alert = WARNING
```

---

### C3. Không cải thiện sau điều trị

```yaml
if:
  medication_started >= 5 days
  AND severity_not_decreasing
then:
  alert = WARNING
```

---

## 3.4 Rule nhóm D – Event-based (Sau tiêm / sau thủ thuật)

### D1. Bình thường (informative)

```yaml
if:
  post_vaccine <= 48h
  AND fever <= 38.5
then:
  alert = INFO
```

---

### D2. Bất thường sau mốc an toàn

```yaml
if:
  post_vaccine >= 72h
  AND fever >= 38
then:
  alert = WARNING
```

---

## 3.5 Rule nhóm E – Risk Score tổng hợp (MVP nâng cao)

### E1. Risk Score Formula (đơn giản)

```text
risk_score =
  symptom_severity_avg * 0.4
+ symptom_duration_days * 0.3
+ missed_checkin_days * 0.2
+ comorbidity_weight * 0.1
```

### Mapping:

* score < 3 → 🟢
* 3–5 → 🟡
* 5–7 → 🟠
* > 7 → 🔴

👉 Dễ explain, dễ tune

---

## 4. Alert Cooldown & Anti-spam

```yaml
alert_cooldown:
  same_rule: 24h
  same_severity: 48h
```

* Không spam
* Ưu tiên alert **severity cao hơn**

---

## 5. Alert Message Template (UX cực quan trọng)

❌ Sai:

> “Bạn có nguy cơ viêm xoang”

✅ Đúng:

> “Triệu chứng nghẹt mũi của bạn kéo dài 5 ngày và đang tăng. Bạn nên cân nhắc đi khám tai–mũi–họng.”

Cấu trúc:

1. Quan sát
2. Lý do
3. Hành động

---

## 6. Rule Engine Architecture (MVP-friendly)

```
Daily Check-in
      ↓
Normalize Symptoms
      ↓
Rule Engine (JSON/YAML)
      ↓
Alert Scoring
      ↓
Notification + Timeline Event
```

* Rule lưu dạng JSON/YAML
* Không hard-code trong code
* Cho phép **medical advisor chỉnh rule**

---

## 7. Lộ trình nâng cấp (sau MVP)

* Rule học từ data (semi-AI)
* Cá nhân hóa threshold
* Explainable AI alert
* Doctor-reviewed rule set

---

## 8. Câu chốt cho sản phẩm

> **“Chúng tôi không phát hiện bệnh – chúng tôi phát hiện khi sức khỏe của bạn đang đi sai hướng.”**

---

Dưới đây là **Architecture chi tiết + Schema DB (MVP → scale được)** cho sản phẩm y tế của bạn, thiết kế theo hướng **rule-based alert + timeline-centric**, tối ưu **chi phí – dễ iterate – an toàn y tế**.

---

# I. Architecture tổng thể (Logical + Physical)

![Image](https://www.researchgate.net/publication/276732354/figure/fig1/AS%3A393453000249356%401470817913889/e-Healthcare-System-Architecture.png)

![Image](https://www.researchgate.net/publication/320621590/figure/fig2/AS%3A553909087203328%401509073625314/Design-Architecture-of-Semantic-Rules-Engine.png)

![Image](https://www.researchgate.net/publication/334272323/figure/fig1/AS%3A797761209380874%401567212502893/System-architecture-of-the-mobile-personal-health-record-app-REST-REpresentational.ppm)

![Image](https://www.researchgate.net/publication/328375487/figure/fig2/AS%3A1023962679083009%401621143135273/App-system-architecture.ppm)

## 1. High-level Architecture (MVP)

```
┌─────────────┐
│ Mobile / Web│
│ (Inertia.js │
│  / Blade)   │
└──────┬──────┘
       │ REST / JSON
       ▼
┌───────────────────┐
│  Laravel API      │
│  (Laravel 12)     │
└──────┬────────────┘
       │
       ├── User Service
       ├── Check-in Service
       ├── Timeline Service
       ├── Rule Engine Service
       └── Notification Service
       │
       ▼
┌─────────────────────────────┐
│ Primary Data Store          │
│ PostgreSQL                  │
└─────────────────────────────┘
       │
       ├── Redis (cache, cooldown)
       ├── Laravel Queue (Redis/DB)
       └── Push (FCM / APNs)
```

---

## 2. Event-driven Flow (Alert 중심)

```
User Check-in
    ↓
Normalize Symptom Data
    ↓
Persist Raw Data
    ↓
Emit HealthEvent
    ↓
Rule Engine
    ↓
Alert Decision
    ↓
Timeline Event + Notification
```

👉 **Key design**:

* **Event first** → dễ mở rộng AI sau
* **Alert không block user flow**

---

# II. Component Breakdown (Chi tiết)

## 1. Client (Web / Mobile)

**Nhiệm vụ**

* Daily check-in (≤30s)
* Timeline view
* Alert inbox
* Knowledge cards

**Không làm**

* Không tính toán alert
* Không lưu logic y tế

---

## 2. API Gateway

**Chức năng**

* Auth (JWT)
* Rate limit
* Validation input y tế
* Versioning API (v1/v2)

---

## 3. Core Services

### 3.1 User Service

* Hồ sơ sức khỏe
* Bệnh nền
* Ngưỡng cá nhân (phase 2)

### 3.2 Check-in Service

* Nhận dữ liệu ngày
* Chuẩn hóa symptom (enum, scale)
* Emit `HealthEvent`

### 3.3 Timeline Service

* Append-only events
* Không update quá khứ (medical integrity)

### 3.4 Rule Engine Service ⭐

* Load rules (JSON/YAML)
* Evaluate theo:

  * timeline
  * trend
  * combination
* Apply cooldown

### 3.5 Notification Service

* Push / Email
* Message template
* Escalation logic

---

# III. Database Schema (PostgreSQL – MVP)

## 1. users

```sql
users (
  id UUID PK,
  email VARCHAR,
  age INT,
  gender VARCHAR,
  conditions JSONB, -- ["hypertension","sinusitis"]
  created_at TIMESTAMP
)
```

---

## 2. daily_checkins

```sql
daily_checkins (
  id UUID PK,
  user_id UUID FK,
  checkin_date DATE,
  overall_feeling INT, -- 1-10
  sleep_hours FLOAT,
  notes TEXT,
  created_at TIMESTAMP,
  UNIQUE(user_id, checkin_date)
)
```

---

## 3. symptoms

```sql
symptoms (
  id UUID PK,
  code VARCHAR,        -- headache, fever
  display_name VARCHAR,
  severity_scale INT, -- usually 10
  is_critical BOOLEAN
)
```

---

## 4. symptom_logs (🔥 timeline raw data)

```sql
symptom_logs (
  id UUID PK,
  user_id UUID FK,
  symptom_code VARCHAR FK,
  severity INT,        -- 0-10
  occurred_at TIMESTAMP,
  source VARCHAR       -- checkin | manual | auto
)
```

👉 **Append-only** – cực quan trọng cho y tế

---

## 5. medications

```sql
medications (
  id UUID PK,
  name VARCHAR,
  type VARCHAR, -- antibiotic, vaccine
  known_side_effects JSONB
)
```

---

## 6. medication_logs

```sql
medication_logs (
  id UUID PK,
  user_id UUID FK,
  medication_id UUID FK,
  taken_at TIMESTAMP,
  missed BOOLEAN
)
```

---

## 7. health_events (Event Bus DB-level)

```sql
health_events (
  id UUID PK,
  user_id UUID,
  event_type VARCHAR,  -- checkin, symptom, medication
  payload JSONB,
  occurred_at TIMESTAMP
)
```

👉 Đây là **xương sống cho Rule Engine**

---

## 8. alert_rules (Config-driven)

```sql
alert_rules (
  id UUID PK,
  code VARCHAR,
  severity VARCHAR, -- info, watch, warning, critical
  condition JSONB,
  cooldown_hours INT,
  is_active BOOLEAN
)
```

📌 Ví dụ `condition`:

```json
{
  "symptom": "fever",
  "min_severity": 38,
  "duration_days": 3
}
```

---

## 9. alerts (Generated output)

```sql
alerts (
  id UUID PK,
  user_id UUID,
  rule_code VARCHAR,
  severity VARCHAR,
  message TEXT,
  triggered_at TIMESTAMP,
  acknowledged_at TIMESTAMP
)
```

---

## 10. timeline_events (User-facing)

```sql
timeline_events (
  id UUID PK,
  user_id UUID,
  event_type VARCHAR, -- symptom, alert, knowledge
  ref_id UUID,
  occurred_at TIMESTAMP
)
```

---

# IV. Rule Engine Execution Model

```
for each HealthEvent:
  load active rules
  for rule in rules:
    if rule.match(event, timeline):
        if not cooldown_hit:
            create Alert
            append TimelineEvent
```

**Key points**

* Stateless per run
* Stateful via DB + Redis
* Deterministic → audit được

---

# V. Redis Usage (Cost-effective)

| Key                          | Purpose       |
| ---------------------------- | ------------- |
| alert:cooldown:{user}:{rule} | Anti-spam     |
| user:risk_score              | cache         |
| last_checkin:{user}          | missing check |

---

# VI. Scaling Path (Sau MVP)

### Phase 2

* Alert personalization
* Risk score ML
* Doctor dashboard

### Phase 3

* Explainable AI
* Wearable integration
* Clinical-grade audit log

---

# VII. Design Principles (Y tế bắt buộc)

* **Append-only medical data**
* **Explainable alerts**
* **Fail-safe (no silent failure)**
* **Rule > Model (MVP)**

---

Dưới đây là **Rule Set hoàn chỉnh (MVP-ready)** cho **3 nhóm bệnh/đối tượng cụ thể** bạn chọn. Thiết kế theo chuẩn **explainable – rule-based – timeline-aware**, dùng được **ngay lập tức** cho product.

---

# I. Viêm mũi dị ứng (Allergic Rhinitis)

![Image](https://my.clevelandclinic.org/-/scassets/images/org/health/articles/8622-allergic-rhinitis-hay-fever)

![Image](https://cdn.sanity.io/images/0vv8moc6/contemporary_clinic/429b5db960bbc6020ae649257e43e6330dbd1953-500x375.jpg?auto=format\&fit=crop)

![Image](https://blog.ohiohealth.com/wp-content/uploads/2017/04/Cold_vs_Allergies_Infographic_695x1024_FINAL.jpg)

## 1. Symptom Model

| Code             | Mô tả     | Scale    |
| ---------------- | --------- | -------- |
| nasal_congestion | Nghẹt mũi | 0–10     |
| sneezing         | Hắt hơi   | lần/ngày |
| runny_nose       | Chảy mũi  | 0–10     |
| itchy_nose       | Ngứa mũi  | 0–10     |
| headache         | Đau đầu   | 0–10     |
| fatigue          | Mệt mỏi   | 0–10     |

---

## 2. Alert Rules

### AR-01 – Triệu chứng kéo dài (chuyển mạn)

```yaml
code: AR_01_PERSISTENT_CONGESTION
if:
  symptom: nasal_congestion
  severity >= 5
  duration_days >= 5
then:
  severity: WARNING
  message: >
    Nghẹt mũi của bạn kéo dài nhiều ngày liên tiếp.
    Viêm mũi dị ứng có thể đang tiến triển nặng hơn.
    Bạn nên cân nhắc đi khám tai–mũi–họng.
```

---

### AR-02 – Pattern điển hình dị ứng

```yaml
code: AR_02_CLASSIC_ALLERGY_PATTERN
if:
  sneezing >= 10
  AND itchy_nose >= 5
  AND runny_nose >= 5
then:
  severity: WATCH
  message: >
    Các triệu chứng của bạn phù hợp với đợt bùng phát viêm mũi dị ứng.
    Hãy theo dõi sát trong 24–48 giờ tới.
```

---

### AR-03 – Nghi ngờ biến chứng xoang

```yaml
code: AR_03_SINUS_COMPLICATION
if:
  nasal_congestion >= 6
  AND headache >= 6
  AND duration_days >= 3
then:
  severity: WARNING
  message: >
    Nghẹt mũi kèm đau đầu kéo dài có thể liên quan đến viêm xoang.
    Bạn nên đi khám để được kiểm tra kỹ hơn.
```

---

### AR-04 – Đêm xấu đi (environmental trigger)

```yaml
code: AR_04_NIGHT_WORSENING
if:
  nasal_congestion_night - nasal_congestion_day >= 3
then:
  severity: INFO
  message: >
    Triệu chứng nặng hơn vào ban đêm có thể liên quan đến môi trường ngủ
    (bụi, điều hòa, độ ẩm).
```

---

# II. Viêm da cơ địa (Atopic Dermatitis)

![Image](https://moreliaclinic.com/wp-content/uploads/2023/09/1_2a510819-2335-4b17-92a6-62604ea50560_grande.png.webp)

![Image](https://cdn.shopify.com/s/files/1/0529/7170/0417/files/hormones-eczema-life-stages-timeline.webp?v=1754678761)

![Image](https://warwickfriendlysociety.com.au/wp-content/uploads/2020/08/eczema-chart.jpg)

## 1. Symptom Model

| Code              | Mô tả              | Scale   |
| ----------------- | ------------------ | ------- |
| itch              | Ngứa               | 0–10    |
| redness           | Đỏ da              | 0–10    |
| oozing            | Rỉ dịch            | boolean |
| cracked_skin      | Nứt da             | boolean |
| sleep_disturbance | Ảnh hưởng giấc ngủ | 0–10    |

---

## 2. Alert Rules

### AD-01 – Đợt bùng phát cấp

```yaml
code: AD_01_ACUTE_FLARE
if:
  itch >= 7
  AND redness >= 6
then:
  severity: WARNING
  message: >
    Da của bạn đang có dấu hiệu bùng phát viêm da cơ địa.
    Tránh gãi và cân nhắc đi khám da liễu nếu không cải thiện.
```

---

### AD-02 – Ngứa ảnh hưởng giấc ngủ (chỉ dấu nặng)

```yaml
code: AD_02_SLEEP_IMPACT
if:
  itch >= 6
  AND sleep_disturbance >= 5
then:
  severity: WARNING
  message: >
    Ngứa da đang ảnh hưởng đến giấc ngủ của bạn,
    đây là dấu hiệu bệnh có xu hướng nặng hơn.
```

---

### AD-03 – Nghi nhiễm trùng da

```yaml
code: AD_03_INFECTION_RISK
if:
  oozing = true
  OR cracked_skin = true
then:
  severity: CRITICAL
  message: >
    Vùng da có dấu hiệu rỉ dịch hoặc nứt nẻ.
    Bạn nên đi khám da liễu sớm để tránh nhiễm trùng.
```

---

### AD-04 – Không cải thiện theo thời gian

```yaml
code: AD_04_NO_IMPROVEMENT
if:
  itch >= 5
  AND duration_days >= 7
then:
  severity: WATCH
  message: >
    Triệu chứng viêm da kéo dài nhiều ngày.
    Việc theo dõi và điều chỉnh điều trị là cần thiết.
```

---

# III. Phụ nữ mang thai (Pregnancy-safe Monitoring)

![Image](https://www.lalpathlabs.com/blog/wp-content/uploads/2016/06/pregnancy-symptoms.jpg)

![Image](https://www.cdc.gov/hearher/images/digital-partner/warning-signs.JPG)

![Image](https://www.envrad.com/content/uploads/2021/12/Womens-Health-Timelines_REV02.png)

⚠️ **Rule nghiêm ngặt – thiên về an toàn**

---

## 1. Symptom Model

| Code             | Mô tả            |
| ---------------- | ---------------- |
| vaginal_bleeding | Ra máu           |
| abdominal_pain   | Đau bụng         |
| severe_headache  | Đau đầu dữ dội   |
| blurred_vision   | Mờ mắt           |
| swelling         | Phù tay/chân/mặt |
| fetal_movement   | Thai máy giảm    |

---

## 2. Alert Rules

### PR-01 – Ra máu khi mang thai

```yaml
code: PR_01_BLEEDING
if:
  vaginal_bleeding = true
then:
  severity: CRITICAL
  message: >
    Bạn có dấu hiệu ra máu trong thai kỳ.
    Hãy đến cơ sở y tế hoặc liên hệ bác sĩ ngay lập tức.
```

---

### PR-02 – Nghi tiền sản giật

```yaml
code: PR_02_PREECLAMPSIA
if:
  severe_headache = true
  AND blurred_vision = true
  AND swelling = true
then:
  severity: CRITICAL
  message: >
    Các triệu chứng của bạn có thể liên quan đến tiền sản giật.
    Đây là tình trạng nguy hiểm cần được kiểm tra ngay.
```

---

### PR-03 – Đau bụng kéo dài

```yaml
code: PR_03_PERSISTENT_PAIN
if:
  abdominal_pain >= 5
  AND duration_hours >= 6
then:
  severity: WARNING
  message: >
    Đau bụng kéo dài trong thai kỳ cần được theo dõi kỹ.
    Bạn nên liên hệ bác sĩ để được tư vấn.
```

---

### PR-04 – Thai máy giảm

```yaml
code: PR_04_REDUCED_FETAL_MOVEMENT
if:
  fetal_movement = decreased
then:
  severity: CRITICAL
  message: >
    Thai máy giảm là dấu hiệu cần được kiểm tra sớm.
    Hãy đến bệnh viện hoặc gọi bác sĩ ngay.
```

---

# IV. Global Safety Rules (Áp dụng chung)

```yaml
if:
  severity = CRITICAL
then:
  bypass_cooldown: true
  push_immediate: true
```

```yaml
if:
  user_ignores_alert >= 2
then:
  escalate_message: true
```

---

# V. Vì sao rule set này phù hợp MVP?

✅ Không chẩn đoán
✅ Giải thích được
✅ Ít false-positive
✅ Phù hợp pháp lý
✅ Dễ review bởi bác sĩ

---

Dưới đây là **thiết kế Timeline UX + Data Model + Logic hiển thị** để **user chỉ cần 5–10 giây là nắm được: “Tôi đang ổn hơn hay tệ hơn?”**, đúng tinh thần sản phẩm y tế của bạn.

---

# 1. Mục tiêu của Timeline (rất quan trọng)

Timeline **KHÔNG** để:

* Liệt kê log y tế ❌
* Xem như medical record ❌

Timeline **PHẢI** trả lời ngay 3 câu hỏi:

1. 📈 **Xu hướng**: đang tốt lên hay xấu đi?
2. ⚠️ **Điểm bất thường**: khi nào bắt đầu?
3. 🧭 **Hành động tiếp theo**: nên làm gì?

👉 **Trend > Event list**

---

# 2. Mental Model cho User

> **Timeline giống “Google Maps cho sức khỏe”**
> Không cần biết mọi chi tiết, chỉ cần:

* Đang đi đúng hướng không?
* Có chỗ nào nguy hiểm phía trước không?

---

# 3. Cấu trúc Timeline (3 lớp – Progressive Disclosure)

![Image](https://cdn.dribbble.com/userupload/45037774/file/8ed946d79fcd2446ad3618e10ac2bd37.png)

![Image](https://i.etsystatic.com/36460112/r/il/e9a3a8/4232695475/il_570xN.4232695475_k6va.jpg)

![Image](https://cdn.prod.website-files.com/62bc8dd8ce4de7cc79849be4/68778335a42fdbbf27f871b3_67f0d0e3f9f61be85bdf09e6_Copy%2520of%2520Dev%2520screenshot%2520stories%2520%287%29.gif)

![Image](https://cdn.prod.website-files.com/59f82cced3f5090001bfcff8/5e4ed8eb5c12865fdca3ccb8_xLzxCE_n1nrdaSDYFy7Z67EkE2-PLqSsIXw4BRi9tM43fAtzRcfu203Phpqce8Bn2ItFgkLNcDq2IbHOtOE4unRIEQGY5eGCaOD-ihmO1y8j9AQcZsZlQgLkPQoS20L3mYVhHLFd.png)

## Layer 1 – Health Status Header (Above the fold)

**Luôn hiển thị trên cùng**

```
🟡 ĐANG THEO DÕI
Triệu chứng chính: Nghẹt mũi (↗ tăng nhẹ)
3 ngày gần đây có xu hướng nặng hơn
```

**Bao gồm**

* Status badge (🟢🟡🟠🔴)
* Symptom chính
* Trend arrow (↗ ↘ →)
* Insight 1 câu (auto-generated)

👉 User hiểu tình trạng **trong 2 giây**

---

## Layer 2 – Visual Trend Strip (7 ngày)

```
Day:  T-6  T-5  T-4  T-3  T-2  T-1  Today
      ▂    ▃    ▄    ▅    ▆    ▆     ▇
```

* Bar / dot chart cực đơn giản
* Mỗi cột = severity tổng hợp ngày
* Màu theo risk

👉 Nhìn **bằng mắt**, không cần đọc

---

## Layer 3 – Event Timeline (Scroll)

```
──────── Today ────────
🔴 Alert: Nghẹt mũi kéo dài 5 ngày
💊 Uống thuốc kháng dị ứng
📘 Gợi ý: Dị ứng theo mùa

──────── Yesterday ─────
🟠 Nghẹt mũi: 6/10
🟠 Hắt hơi: 12 lần
😴 Ngủ kém

──────── 3 days ago ────
🟡 Nghẹt mũi: 4/10
```

* Chỉ hiển thị **event có ý nghĩa**
* Không spam raw logs

---

# 4. Event Types & Icon System (chuẩn hóa)

| Type       | Icon  | Ý nghĩa           |
| ---------- | ----- | ----------------- |
| Symptom    | 🤧 🩹 | Thay đổi sức khỏe |
| Alert      | ⚠️ 🔴 | Điểm cần chú ý    |
| Medication | 💊    | Can thiệp         |
| Knowledge  | 📘    | Hiểu thêm         |
| Milestone  | 🏁    | Cột mốc           |

👉 User nhận biết bằng icon, không cần đọc chữ

---

# 5. Insight Engine (Timeline không chỉ là UI)

Mỗi ngày sinh **1 Insight tự động**:

### Ví dụ (viêm mũi dị ứng)

> “Triệu chứng nghẹt mũi của bạn tăng dần trong 3 ngày gần đây, đặc biệt nặng hơn vào ban đêm.”

**Rule sinh insight**

```yaml
if:
  severity_trend = increasing
  AND night > day
then:
  insight_type: trend
```

👉 Timeline = **story**, không phải log

---

# 6. Drill-down có kiểm soát (Optional)

User tap vào event:

### Alert Detail

* Vì sao alert xuất hiện
* Rule nào kích hoạt
* Dữ liệu liên quan (3 ngày)

⚠️ **Không hiển thị thuật ngữ y khoa khó**

---

# 7. Timeline cho 3 nhóm bạn chọn

## 7.1 Viêm mũi dị ứng

* Highlight: **ngày ↔ đêm**
* Gắn knowledge card theo mùa
* Group symptom theo “mũi – đầu – mệt”

## 7.2 Viêm da cơ địa

* Group theo **đợt bùng phát**
* Đánh dấu “flare start / flare end”
* Nhấn mạnh ảnh hưởng giấc ngủ

## 7.3 Phụ nữ mang bầu

* Timeline **theo tuần thai**
* Event nguy hiểm luôn **pin trên top**
* Ít dữ liệu nhưng **severity cao**

---

# 8. Data Model cho Timeline (Mapping với DB)

```text
timeline_event {
  id
  user_id
  occurred_at
  type: symptom | alert | medication | insight
  severity: info | watch | warning | critical
  title
  summary
  ref_id
}
```

👉 Timeline chỉ consume **processed data**, không raw

---

# 9. Performance & UX cho MVP

* Load mặc định: 7 ngày
* Lazy load theo scroll
* Cache insight 24h
* Skeleton UI (y tế rất cần cảm giác tin cậy)

---

# 10. Nguyên tắc vàng (rất quan trọng)

✅ Timeline phải:

* Ít chữ
* Nhiều tín hiệu
* Có nhịp điệu (rhythm)

❌ Timeline không được:

* Là medical chart
* Khiến user lo lắng vô cớ
* Bắt user “đọc và suy nghĩ”

---
