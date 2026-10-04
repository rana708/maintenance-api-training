# Entity Relationship Diagram (ERD) - HVAC Maintenance System

## 1. Mermaid ERD Diagram

```mermaid
erDiagram
    users ||--o{ visit_assignments : "assigned as technician"
    users ||--o{ technical_reports : "writes report"
    users ||--o{ request_status_histories : "changes status"

    customers ||--o{ customer_locations : "has many"
    customers ||--o{ maintenance_contracts : "owns"
    customers ||--o{ maintenance_requests : "requests"

    customer_locations ||--o{ maintenance_requests : "location for"

    maintenance_contracts ||--o{ contract_visit_schedules : "generates"
    maintenance_requests ||--o{ visit_assignments : "has many assignments"
    maintenance_requests ||--o{ spare_part_requests : "requires"
    maintenance_requests ||--o| technical_reports : "has final report"
    maintenance_requests ||--o{ request_status_histories : "tracks history"

    users {
        bigint id PK
        string name
        string email
        string phone
        enum role "dispatcher|technician|admin"
        enum status "active|inactive"
        timestamp created_at
        timestamp updated_at
    }

    customers {
        bigint id PK
        string name
        string phone_number
        timestamp created_at
        timestamp updated_at
    }

    customer_locations {
        bigint id PK
        bigint customer_id FK
        string title
        string address
        string gps_coordinates
        boolean is_primary
        timestamp created_at
        timestamp updated_at
    }

    maintenance_contracts {
        bigint id PK
        bigint customer_id FK
        date contract_start
        date contract_end
        integer total_annual_visits
        integer remaining_visits
        enum status "active|expired|cancelled"
        timestamp created_at
        timestamp updated_at
    }

    contract_visit_schedules {
        bigint id PK
        bigint maintenance_contract_id FK
        enum visit_type "summer|winter"
        date scheduled_date
        enum status "draft|confirmed|completed|cancelled"
        timestamp created_at
        timestamp updated_at
    }

    maintenance_requests {
        bigint id PK
        bigint customer_id FK
        bigint location_id FK
        bigint technician_id FK "nullable"
        bigint maintenance_contract_id FK "nullable"
        string request_type
        enum priority "normal|emergency"
        enum status "new|assigned|in_progress|pending_spare_parts|completed|cancelled|reassigned"
        text issue_description
        datetime preferred_schedule_at
        boolean requires_dual_technicians
        text dispatcher_notes
        timestamp created_at
        timestamp updated_at
    }

    visit_assignments {
        bigint id PK
        bigint maintenance_request_id FK
        bigint technician_id FK
        enum assignment_role "primary|assistant"
        datetime scheduled_at
        datetime started_at "nullable"
        datetime ended_at "nullable"
        enum status "scheduled|in_progress|completed|cancelled"
        timestamp created_at
        timestamp updated_at
    }

    spare_part_requests {
        bigint id PK
        bigint maintenance_request_id FK
        string part_name
        string part_image_url
        text notes "nullable"
        enum status "pending|approved|fulfilled|rejected"
        timestamp created_at
        timestamp updated_at
    }

    technical_reports {
        bigint id PK
        bigint maintenance_request_id FK
        bigint technician_id FK
        text report_text
        string ac_status
        string gps_coordinates
        timestamp created_at
        timestamp updated_at
    }

    request_status_histories {
        bigint id PK
        bigint maintenance_request_id FK
        bigint changed_by_user_id FK
        enum old_status "new|assigned|in_progress|pending_spare_parts|completed|cancelled|reassigned"
        enum new_status "new|assigned|in_progress|pending_spare_parts|completed|cancelled|reassigned"
        text note "nullable"
        timestamp created_at
    }

2. توضيح الجداول، المفاتيح الأجنبية، والفهارس
 أ. جدول 
 maintenance_requests
   المفاتيح الأجنبية (FKs):
       customer_id $\rightarrow$ customers(id) ON DELETE RESTRICT (حماية سجلات العملاء من الحذف التلقائي).
       location_id $\rightarrow$ customer_locations(id) ON DELETE RESTRICT.
    الفهارس (Indexes):
         INDEX idx_requests_status_priority (status, priority): لتسريع استعلامات تصفية الذروة والأولويات (Story 4).
         INDEX idx_requests_customer (customer_id): لجلب كافة طلبات العميل بسرعة.
ب. جدول 
visit_assignments
   المفاتيح الأجنبية (FKs): 
          maintenance_request_id $\rightarrow$ maintenance_requests(id) ON DELETE CASCADE. 
          technician_id $\rightarrow$ users(id) ON DELETE RESTRICT. 
    الفهارس والقيود (Indexes & Constraints):
           UNIQUE INDEX uq_tech_schedule (technician_id, scheduled_at): تمثيل حالة حدية من الـ PRD (قاعدة 6.1) يضمن منع تعيين الفني لأكثر من زيارة في نفس الساعة على مستوى قاعدة البيانات (نسبة 0% تضارب).
          INDEX idx_assignments_tech_date (technician_id, scheduled_at, status): لتسريع عرض جدول الزيارات اليومية للفني على تطبيق الموبايل.

 ج. جدول 
   spare_part_requests 
     المفاتيح الأجنبية (FKs):
          maintenance_request_id $\rightarrow$ maintenance_requests(id) ON DELETE CASCADE. 
 د. جدول 
 maintenance_contracts    
     المفاتيح الأجنبية (FKs):
            customer_id $\rightarrow$ customers(id) ON DELETE RESTRICT.
     الفهارس (Indexes):     
            INDEX idx_contracts_auto_draft (status, contract_start): لتسريع المهمة المجدولة (Cron Job) لتوليد مسودات الزيارات الموسمية قبل موعدها بـ 14 يوماً (Story 3).

        
3. قسم تعديلات على اقتراح الأداة (Justified Modifications)
  1-تعديل 1: الاعتماد على جدول فرعي للتعيينات visit_assignments بدلاً من حقل مفتاح أجنبي مباشر technician_id في جدول الطلبات.
      السبب المباشر: الأداة اقترحت حقل technician_id منفرد بداخل maintenance_requests. هذا المخطط كان سيفشل في تحقيق Story 1 والقاعدة 6.1 والتي تشترط إمكانية تعيين فنيين اثنين (رئيسي ومساعد primary/assistant) على طلب صيانة واحد بدون تكرار الصفوف الأساسية للطلب.
  2-تعديل 2: فصل عناوين الموقع في جدول customer_locations وإضافة جدول تتبع الحالات request_status_histories.
       السبب المباشر: الذكاء الاصطناعي دمج العنوان داخل جدول العميل مباشرة. الـ PRD ينص في القسم 8.3 و 12.1 على تعدد المواقع للعميل الواحد. كما أغفلت الأداة سجل تدقيق الحالات (Audit Trail)، وتم إنشاء request_status_histories لضمان تتبع الانتقال بين الحالات الحساسة مثل Pending - Spare Parts و Reassigned.
       
          
