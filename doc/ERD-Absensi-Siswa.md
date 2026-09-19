# ERD — Database Aplikasi Absensi Siswa

```mermaid
erDiagram
    USERS ||--o| TEACHERS : "punya profil"
    USERS ||--o| STUDENTS : "punya profil"
    USERS ||--o| PARENTS : "punya profil"

    ACADEMIC_YEARS ||--o{ SCHOOL_CLASSES : "punya"
    SCHOOL_CLASSES ||--o{ STUDENTS : "berisi"
    TEACHERS ||--o{ SCHOOL_CLASSES : "wali kelas"
    TEACHERS ||--o{ SCHEDULES : "mengajar"
    SUBJECTS ||--o{ SCHEDULES : "dipakai di"
    SCHOOL_CLASSES ||--o{ SCHEDULES : "punya jadwal"

    STUDENTS ||--o{ STUDENT_PARENT : "punya"
    PARENTS ||--o{ STUDENT_PARENT : "punya"

    STUDENTS ||--o{ QR_TOKENS : "punya"
    STUDENTS ||--o{ ATTENDANCES : "dicatat"
    SCHEDULES ||--o{ ATTENDANCES : "konteks jam"
    USERS ||--o{ ATTENDANCES : "input/record_by"

    STUDENTS ||--o{ LEAVE_REQUESTS : "mengajukan"
    USERS ||--o{ LEAVE_REQUESTS : "reviewed_by"

    PARENTS ||--o{ NOTIFICATION_LOGS : "menerima"
    ATTENDANCES ||--o{ NOTIFICATION_LOGS : "memicu"
    LEAVE_REQUESTS ||--o{ NOTIFICATION_LOGS : "memicu"

    USERS {
        bigint id PK
        string name
        string email
        string password
        enum role "admin, guru, siswa, orangtua"
        boolean is_active
    }
    TEACHERS {
        bigint id PK
        bigint user_id FK
        string nip
        string phone
    }
    STUDENTS {
        bigint id PK
        bigint user_id FK
        string nis
        string nisn
        bigint school_class_id FK
        string qr_code_identifier UK
        date birth_date
        enum gender
    }
    PARENTS {
        bigint id PK
        bigint user_id FK
        string phone
        enum relation "ayah, ibu, wali"
    }
    STUDENT_PARENT {
        bigint id PK
        bigint student_id FK
        bigint parent_id FK
    }
    ACADEMIC_YEARS {
        bigint id PK
        string name
        enum semester "ganjil, genap"
        date start_date
        date end_date
        boolean is_active
    }
    SCHOOL_CLASSES {
        bigint id PK
        bigint academic_year_id FK
        string name
        enum level "SD, SMP, SMA"
        bigint homeroom_teacher_id FK
    }
    SUBJECTS {
        bigint id PK
        string name
        string code
    }
    SCHEDULES {
        bigint id PK
        bigint school_class_id FK
        bigint subject_id FK
        bigint teacher_id FK
        tinyint day_of_week
        time start_time
        time end_time
    }
    ATTENDANCE_SETTINGS {
        bigint id PK
        enum mode "daily, per_lesson"
        int tolerance_minutes
    }
    QR_TOKENS {
        bigint id PK
        bigint student_id FK
        string token UK
        datetime expires_at
        boolean is_used
    }
    ATTENDANCES {
        bigint id PK
        bigint student_id FK
        bigint schedule_id FK
        date date
        datetime check_in_time
        datetime check_out_time
        enum status "hadir, sakit, izin, alpa, terlambat"
        enum method "manual, qr"
        bigint recorded_by FK
        text notes
    }
    LEAVE_REQUESTS {
        bigint id PK
        bigint student_id FK
        bigint requested_by FK
        enum type "sakit, izin"
        text reason
        string attachment_path
        date date_from
        date date_to
        enum status "pending, approved, rejected"
        bigint reviewed_by FK
        datetime reviewed_at
    }
    NOTIFICATION_LOGS {
        bigint id PK
        bigint parent_id FK
        bigint attendance_id FK
        bigint leave_request_id FK
        enum channel "whatsapp, email"
        enum status "sent, failed, pending"
        text message
        datetime sent_at
    }
```

## Catatan Desain

- **users** memakai kolom `role` sederhana (enum) untuk versi awal. Jika kebutuhan hak akses makin kompleks (misal: kepala sekolah, guru piket, guru BK), pertimbangkan migrasi ke package `spatie/laravel-permission` di fase 2.
- **students.qr_code_identifier** adalah ID unik permanen siswa (dicetak di kartu). **qr_tokens** terpisah, berisi token yang **berubah/expired** — ini yang benar-benar dipindai tiap hari agar QR tidak bisa difoto lalu dipakai titip absen oleh orang lain.
- **attendances.schedule_id** bersifat nullable: diisi jika mode absensi "per_lesson" (SMP/SMA), dikosongkan jika mode "daily" (SD/MI, satu baris absensi per hari per siswa).
- **attendance_settings** didesain sebagai tabel 1 baris (school-wide config) karena single-tenant. Jika nanti multi-tenant, tabel ini butuh kolom `school_id`.
- Semua tabel relasi (teachers, students, parents) memisahkan **profil** dari **users** agar tabel `users` tetap ringan dan fokus ke autentikasi.
