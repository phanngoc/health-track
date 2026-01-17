Dưới đây là **thiết kế Insight Engine chuyên sâu** – phần **“trí não”** biến log y tế thành **nhận thức + định hướng hành động**, đúng với mục tiêu:
👉 *User mở app 5–10 giây là “hiểu mình đang thế nào và nên làm gì”*.

---

# 1. Insight Engine là gì (định nghĩa chuẩn)

> **Insight ≠ Alert**
> **Insight = diễn giải có ngữ cảnh + xu hướng + mức độ trấn an hoặc cảnh giác**

|           | Alert     | Insight                |
| --------- | --------- | ---------------------- |
| Mục đích  | Cảnh báo  | Hiểu diễn biến         |
| Kích hoạt | Rule cứng | Phân tích mềm          |
| Tần suất  | Ít        | Hằng ngày              |
| Cảm xúc   | Cảnh giác | Bình tĩnh – định hướng |

👉 **Timeline sống nhờ Insight, không phải Alert**

---

# 2. Vị trí Insight trong kiến trúc

![Image](https://d2908q01vomqb2.cloudfront.net/77de68daecd823babbb58edb1c8e14d7106e83bb/2023/09/26/PHISimage001.png)

![Image](https://www.researchgate.net/publication/394251900/figure/fig4/AS%3A11431281626152187%401757329857724/System-model-for-integrating-explainable-AI-in-Clinical-Decision-Support-Systems.png)

![Image](https://www.researchgate.net/publication/380212335/figure/fig1/AS%3A11431281239954146%401714532457551/Real-world-data-pipeline-Prior-to-commencing-the-analysis-the-clinical-experts.png)

```
Raw Data (check-in, symptom)
        ↓
Aggregation (day / 3d / 7d)
        ↓
Trend & Pattern Analyzer
        ↓
Context Filter (disease, pregnancy)
        ↓
Insight Generator
        ↓
Timeline + Header Summary
```

---

# 3. Phân loại Insight (taxonomy – rất quan trọng)

## 3.1 Trend Insight – “Đang tốt lên hay xấu đi?”

```yaml
type: TREND
examples:
  - improving
  - worsening
  - stable
```

📌 Ví dụ:

> “Triệu chứng nghẹt mũi của bạn **tăng dần trong 3 ngày gần đây**.”

---

## 3.2 Pattern Insight – “Có quy luật gì không?”

```yaml
type: PATTERN
examples:
  - night_worse_than_day
  - weekday_vs_weekend
  - post_medication_change
```

📌 Ví dụ:

> “Triệu chứng của bạn **nặng hơn rõ rệt vào ban đêm**.”

---

## 3.3 Comparison Insight – “So với chính bạn”

```yaml
type: COMPARISON
baseline:
  - last_week
  - personal_average
```

📌 Ví dụ:

> “Mức độ ngứa da hiện tại **cao hơn trung bình tuần trước**.”

---

## 3.4 Contextual Insight – “Với tình trạng của bạn thì điều này có ý nghĩa gì”

```yaml
type: CONTEXTUAL
context:
  - disease_specific
  - pregnancy_safe
```

📌 Ví dụ (mang thai):

> “Đau đầu nhẹ có thể gặp trong thai kỳ, nhưng cần theo dõi nếu kéo dài.”

---

## 3.5 Reassurance Insight – “Trấn an có kiểm soát” ⭐

```yaml
type: REASSURANCE
```

📌 Ví dụ:

> “Triệu chứng của bạn đang **ổn định và trong mức thường gặp**.”

👉 Cực kỳ quan trọng để **giữ trust**

---

# 4. Insight Pipeline chi tiết (Logic thật)

## 4.1 Aggregation Layer

```text
daily_score = weighted_avg(symptoms)
3d_avg = avg(daily_score[-3:])
7d_avg = avg(daily_score[-7:])
```

* Không dùng raw log
* Luôn normalize về 0–10

---

## 4.2 Trend Analyzer

```yaml
if 3d_avg - 7d_avg >= +1.5:
  trend = worsening
elif 7d_avg - 3d_avg >= +1.5:
  trend = improving
else:
  trend = stable
```

👉 Threshold **coarse**, tránh false insight

---

## 4.3 Pattern Detector (rule-based)

### Ví dụ: Night vs Day

```yaml
if:
  avg(night_severity) - avg(day_severity) >= 2
then:
  pattern: night_worsening
```

### Ví dụ: Sau khi dùng thuốc

```yaml
if:
  medication_started <= 3 days
  AND severity_decreasing
then:
  pattern: positive_response
```

---

## 4.4 Context Filter (rất quan trọng cho phụ nữ mang bầu)

```yaml
if user_context = pregnancy:
  disable:
    - reassurance_on_bleeding
    - reassurance_on_pain
```

👉 Không bao giờ “trấn an nhầm”

---

# 5. Insight Generation Rules (YAML thực tế)

### 5.1 Worsening Trend Insight

```yaml
code: INS_TREND_WORSENING
when:
  trend: worsening
  duration_days >= 3
then:
  priority: high
  message: >
    Triệu chứng của bạn đang có xu hướng nặng hơn trong vài ngày gần đây.
    Hãy theo dõi sát và cân nhắc trao đổi với bác sĩ nếu tiếp diễn.
```

---

### 5.2 Stable Reassurance Insight

```yaml
code: INS_STABLE_REASSURE
when:
  trend: stable
  no_critical_alerts
then:
  priority: low
  message: >
    Tình trạng sức khỏe của bạn đang ổn định trong những ngày gần đây.
```

---

### 5.3 Disease-specific Insight (Viêm da cơ địa)

```yaml
code: INS_AD_SLEEP_IMPACT
when:
  itch >= 6
  sleep_disturbance >= 5
then:
  priority: medium
  message: >
    Ngứa da đang ảnh hưởng đến giấc ngủ, đây là dấu hiệu viêm da cơ địa
    có xu hướng bùng phát.
```

---

# 6. Insight Ranking & Deduplication

👉 **Mỗi ngày tối đa 1–2 insight**

### Ranking score

```text
score =
  severity_weight
+ trend_strength
+ context_risk
- repetition_penalty
```

* Ưu tiên:

  * Insight mới
  * Insight có hành động gợi ý
* Không lặp cùng message trong 48h

---

# 7. Insight ↔ Timeline Integration

## Header Insight (Above the fold)

* Chỉ 1 câu
* Ưu tiên TREND hoặc REASSURANCE

## Timeline Insight Card

* Có icon 🧠
* Tap để xem “Vì sao”

---

# 8. Explainability (bắt buộc cho y tế)

Khi user tap “Vì sao tôi thấy insight này?”

Hiển thị:

* Dữ liệu dùng (3 ngày)
* Quy tắc đơn giản
* Không hiển thị thuật toán

📌 Ví dụ:

> “Dựa trên mức nghẹt mũi bạn ghi nhận trong 3 ngày gần đây.”

---

# 9. Insight cho 3 nhóm bệnh (tóm tắt nhanh)

## Viêm mũi dị ứng

* Night vs Day
* Theo mùa
* Sau thay đổi môi trường

## Viêm da cơ địa

* Flare start / end
* Sleep impact
* Không cải thiện sau 7 ngày

## Phụ nữ mang bầu

* Conservative insight
* Ít reassurance
* Ưu tiên safety

---

# 10. Nguyên tắc vàng (đóng đinh)

✅ Insight phải:

* Đúng
* Dễ hiểu
* Không làm user hoảng

❌ Insight không được:

* Chẩn đoán
* Hứa hẹn
* Nói mơ hồ

