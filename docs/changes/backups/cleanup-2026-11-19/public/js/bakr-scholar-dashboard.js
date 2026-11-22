/* BAKR — Scholar Dashboard (scholarDashboardF). Moved out of the Blade view's inline <script> blocks (same order). */

/* =====================================================================
     SCHOLAR DATABASE (shared by every view)
     One object whose keys are the proposed tables and whose fields are
     the exact column names and formats from the ERD:
       date      -> "YYYY-MM-DD"
       datetime  -> "YYYY-MM-DD HH:MM:SS"
       decimal   -> number
       boolean   -> true / false
       FK        -> the id of the related row
     Replace the rows below with the rows loaded for the signed-in scholar
     (scholars.scholar_id). Nothing else in the page needs to change.
     Not loaded on purpose: users.password_hash, audit_logs, applications,
     payout_accounts, officers and admins (the scholar dashboard does not
     display them).
     ===================================================================== */
(function() {
  "use strict";

  var DB = {
    campuses: [{
      campus_id: 1,
      campus_name: "DNSC (Main Campus)"
    }],

    academic_programs: [{
      program_id: 1,
      program_code: "BSIT",
      program_name: "BS Information Technology",
      institute: "Institute of Computing"
    }],

    academic_terms: [{
      term_id: 1,
      academic_year: "2026-2027",
      semester: "1st Semester",
      document_deadline: "2026-09-25",
      is_current: true
    }],

    scholarships: [{
      scholarship_id: 1,
      scholarship_code: "CLFI",
      scholarship_name: "CLFI National Scholarship Program",
      category: "National",
      release_frequency: "Per month",
      min_amount: 5000.00,
      max_amount: 5000.00,
      is_active: true
    }],

    scholarship_benefits: [{
      benefit_id: 1,
      scholarship_id: 1,
      benefit_name: "Monthly Stipend",
      benefit_type: "stipend",
      description: "Monthly allowance released to the scholar for the duration of the scholarship.",
      amount: 5000.00
    }, {
      benefit_id: 2,
      scholarship_id: 1,
      benefit_name: "Accident & Health Insurance",
      benefit_type: "other",
      description: "Coverage for the duration of the scholarship.",
      amount: null
    }],

    scholarship_requirements: [{
      requirement_id: 1,
      scholarship_id: 1,
      document_name: "24K Loyalty Card",
      description: "Your Cebuana Lhuillier 24K Loyalty Card. Upload a clear photo or scan of the front and back of the card.",
      where_to_get: "Cebuana Lhuillier branch. Use the card you already have, or ask a branch about getting one.",
      processing_time: "Ready right away if you already have a card. A new card may take longer."
    }, {
      requirement_id: 2,
      scholarship_id: 1,
      document_name: "Proof of income",
      description: "Shows your family's income so CLFI can confirm that you still qualify for the program.",
      where_to_get: "Your parent or guardian's employer, or the Barangay Hall (e.g. a Certificate of Indigency).",
      processing_time: "Usually issued within 1–3 working days."
    }, {
      requirement_id: 3,
      scholarship_id: 1,
      document_name: "Grades (GWA of 85% with no failing mark)",
      description: "Shows that your general weighted average (GWA) is at least 85% and that you have no failing mark.",
      where_to_get: "Registrar's Office / Student Portal",
      processing_time: "Usually released after each term. A certified copy takes 3–5 working days."
    }, {
      requirement_id: 4,
      scholarship_id: 1,
      document_name: "Good Moral certificate",
      description: "Shows that you are in good standing at your school, with no unresolved disciplinary records.",
      where_to_get: "Guidance Office / Student Affairs Office",
      processing_time: "Usually released within 2–3 working days after you request it."
    }, {
      requirement_id: 5,
      scholarship_id: 1,
      document_name: "Enrollment form",
      description: "Confirms that you are currently enrolled in your degree program for this term.",
      where_to_get: "Registrar's Office / Student Portal",
      processing_time: "Usually issued within 1–2 working days."
    }, {
      requirement_id: 6,
      scholarship_id: 1,
      document_name: "Grading system",
      description: "The school's grading system, so the office can read your grades and check the 85% GWA requirement.",
      where_to_get: "Registrar's Office",
      processing_time: "Usually issued within 1–3 working days."
    }],

    scholarship_maintenance_requirements: [{
      maintenance_id: 1,
      scholarship_id: 1,
      category: "Academic Requirements",
      title: "Maintain required GPA",
      description: "Keep your general weighted average (GWA) at or above the minimum set by the scholarship program. Your grades are reviewed at the end of every semester, and falling below the minimum puts your scholarship under review."
    }, {
      maintenance_id: 2,
      scholarship_id: 1,
      category: "Academic Requirements",
      title: "Pass required units",
      description: "Pass the minimum number of units required for each term. Failed, dropped, or incomplete subjects may count against your academic standing."
    }, {
      maintenance_id: 3,
      scholarship_id: 1,
      category: "Enrollment Requirements",
      title: "Remain enrolled full-time",
      description: "Stay enrolled in a full academic load in your degree program each term. Part-time enrollment is not covered unless the scholarship office approves it in writing."
    }, {
      maintenance_id: 4,
      scholarship_id: 1,
      category: "Enrollment Requirements",
      title: "Maintain active student status",
      description: "Keep your student status active and in good standing with your school. A leave of absence, suspension, or dismissal must be reported to the scholarship office."
    }, {
      maintenance_id: 5,
      scholarship_id: 1,
      category: "Compliance Requirements",
      title: "Submit grades every semester",
      description: "Upload your official grades or report card as soon as your school releases them. Your stipend for the next term depends on it."
    }, {
      maintenance_id: 6,
      scholarship_id: 1,
      category: "Compliance Requirements",
      title: "Attend required orientations",
      description: "Join the orientations and briefings the scholarship office schedules for scholars. Dates are sent through your notifications. Tell the office ahead of time if you cannot attend."
    }],

    scholars: [{
      scholar_id: 1,
      user_id: 1,
      scholar_number: "SCH-2026-00124",
      student_id: "2026-00124",
      first_name: "Angelo",
      middle_name: "Karl",
      last_name: "Santos",
      email: "angelo.santos@gmail.com",
      alternative_email: null,
      phone_number: "0917 123 4567",
      birth_date: "2006-03-14",
      age: 20,
      sex: "Male",
      nationality: "Filipino",
      home_address: "Blk 5 Lot 12, Sampaguita St., Brgy. Guinhawa",
      city: "Malolos City",
      province: "Bulacan",
      zip_code: "3000",
      campus_id: 1,
      scholar_status: "Active"
    }],

    enrollments: [{
      enrollment_id: 1,
      scholar_id: 1,
      program_id: 1,
      term_id: 1,
      year_level: 1,
      gwa: 1.50,
      renewal_status: "Eligible"
    }],

    scholarship_awards: [{
      award_id: 1,
      scholar_id: 1,
      scholarship_id: 1,
      classification_id: null,
      award_status: "Approved",
      start_date: "2026-08-01"
    }],

    requirement_submissions: [{
      submission_id: 1,
      award_id: 1,
      requirement_id: 2,
      term_id: 1,
      version_no: 1,
      file_path: "submissions/1/proof-of-income.pdf",
      verification_status: "rejected",
      reviewer_note: "The uploaded document is unclear. Upload a clearer copy of your proof of income.",
      submitted_at: "2026-09-12 10:15:00",
      verified_by_officer_id: 1,
      verified_at: "2026-09-14 14:20:00"
    }, {
      submission_id: 2,
      award_id: 1,
      requirement_id: 3,
      term_id: 1,
      version_no: 1,
      file_path: "submissions/1/report-card.pdf",
      verification_status: "pending",
      reviewer_note: null,
      submitted_at: "2026-09-16 09:40:00",
      verified_by_officer_id: null,
      verified_at: null
    }, {
      submission_id: 3,
      award_id: 1,
      requirement_id: 5,
      term_id: 1,
      version_no: 1,
      file_path: "submissions/1/enrollment-form.pdf",
      verification_status: "verified",
      reviewer_note: null,
      submitted_at: "2026-08-28 11:05:00",
      verified_by_officer_id: 1,
      verified_at: "2026-08-29 09:00:00"
    }, {
      submission_id: 4,
      award_id: 1,
      requirement_id: 1,
      term_id: 1,
      version_no: 1,
      file_path: "submissions/1/loyalty-card.jpg",
      verification_status: "verified",
      reviewer_note: null,
      submitted_at: "2026-08-28 11:10:00",
      verified_by_officer_id: 1,
      verified_at: "2026-08-29 09:05:00"
    }, {
      submission_id: 5,
      award_id: 1,
      requirement_id: 4,
      term_id: 1,
      version_no: 1,
      file_path: "submissions/1/good-moral-certificate.pdf",
      verification_status: "verified",
      reviewer_note: null,
      submitted_at: "2026-08-30 15:30:00",
      verified_by_officer_id: 1,
      verified_at: "2026-08-31 10:00:00"
    }],

    disbursements: [{
      disbursement_id: 1,
      award_id: 1,
      payout_account_id: 1,
      term_id: 1,
      released_by_officer_id: 1,
      release_period: "September 2026",
      amount: 5000.00,
      funding_source: "CLFI Foundation",
      scheduled_release_date: "2026-09-05",
      release_date: "2026-09-05",
      credited_at: "2026-09-05 10:30:00",
      disbursement_status: "Released",
      failure_reason: null
    }, {
      disbursement_id: 2,
      award_id: 1,
      payout_account_id: 1,
      term_id: 1,
      released_by_officer_id: null,
      release_period: "October 2026",
      amount: 5000.00,
      funding_source: "CLFI Foundation",
      scheduled_release_date: "2026-10-05",
      release_date: null,
      credited_at: null,
      disbursement_status: "Scheduled",
      failure_reason: null
    }],

    /* notifications addressed to this scholar: audience_role = "scholar"
       and scholar_id = scholars.scholar_id. read_at = null means unread. */
    notifications: [{
      notification_id: 1,
      recipient_officer_id: null,
      recipient_admin_id: null,
      audience_role: "scholar",
      scholar_id: 1,
      notification_type: "document_deadline",
      title: "Your documentary requirement deadline is approaching.",
      message: "Grading system is due in 3 days.",
      entity_id: 6,
      created_at: "2026-09-24 09:00:00",
      read_at: null
    }, {
      notification_id: 2,
      recipient_officer_id: null,
      recipient_admin_id: null,
      audience_role: "scholar",
      scholar_id: 1,
      notification_type: "resubmission",
      title: "A document requires resubmission.",
      message: "Your Proof of income needs to be resubmitted. The uploaded document is unclear.",
      entity_id: 2,
      created_at: "2026-09-22 14:20:00",
      read_at: null
    }, {
      notification_id: 3,
      recipient_officer_id: null,
      recipient_admin_id: null,
      audience_role: "scholar",
      scholar_id: 1,
      notification_type: "stipend",
      title: "Your stipend is scheduled for release.",
      message: "Expected on October 5, 2026.",
      entity_id: 2,
      created_at: "2026-09-20 08:00:00",
      read_at: null
    }, {
      notification_id: 4,
      recipient_officer_id: null,
      recipient_admin_id: null,
      audience_role: "scholar",
      scholar_id: 1,
      notification_type: "stipend",
      title: "Your stipend has been released.",
      message: "₱5,000 for 2nd Semester, AY 2025-2026.",
      entity_id: 1,
      created_at: "2026-03-12 10:30:00",
      read_at: "2026-03-12 18:00:00"
    }, {
      notification_id: 5,
      recipient_officer_id: null,
      recipient_admin_id: null,
      audience_role: "scholar",
      scholar_id: 1,
      notification_type: "document_deadline",
      title: "Your documentary requirement deadline is approaching.",
      message: "Enrollment form is due in 5 days.",
      entity_id: 5,
      created_at: "2026-08-24 09:00:00",
      read_at: "2026-08-24 12:00:00"
    }, {
      notification_id: 6,
      recipient_officer_id: null,
      recipient_admin_id: null,
      audience_role: "scholar",
      scholar_id: 1,
      notification_type: "resubmission",
      title: "A document requires resubmission.",
      message: "Your 24K Loyalty Card needs to be resubmitted. Please submit an updated copy.",
      entity_id: 1,
      created_at: "2026-08-10 13:00:00",
      read_at: "2026-08-10 15:00:00"
    }]
  };

  /* =====================================================================
     DEMO ACCOUNTS (sample data only, nothing here is real)
     Rows for two extra scholars are appended to the same tables above,
     using the same column names and formats. Both accounts start empty. Scholarship name, code, category,
     amount, benefits and maintaining requirements follow the
     admin dashboard's PROGRAM_CONFIGS; name, category, description, DNSC
     institutes and courses follow the Scholarship Finder. The documents a scholar uploads are the
     ones that prove those maintaining requirements, and they differ per
     scholarship (CHED Merit: COR, Certificate of Ratings, Landbank Card, Student ID; BPMSP: COR/COE, grades, no-other-scholarship certification, plus the conditional and graduation forms; CLFI: 24K Loyalty Card, proof of income, grades, good moral, enrollment form, grading system), the same lists as the Admin and Officer dashboards. The scholar shown is the
     one whose email was used at sign-in (see scholarLogin). Opening this
     page directly, without signing in, still shows scholar_id 1.
       scholar_id 2  Karl Angelo T. Gabaes   CHED Merit Scholarship Program (Half Scholar, CMSP), BSIS
       scholar_id 3  Rochelle D. Abella      Bagong Pilipinas Merit Scholarship Program (BPMSP), BSFT
     ===================================================================== */
  DB.academic_programs.push({
    program_id: 2, program_code: "BSIS", program_name: "Bachelor of Science in Information Systems (BSIS)", institute: "Institute of Computing (IC)"
  }, {
    program_id: 3, program_code: "BSFT", program_name: "Bachelor of Science in Food Technology (BSFT)", institute: "Institute of Aquatic and Applied Sciences (IAAS)"
  });

  DB.scholarships.push({
    scholarship_id: 2, scholarship_code: "CMSP",
    scholarship_name: "CHED Merit Scholarship Program",
    description: "Competitive CHED scholarship for academically talented incoming first-year college students.",
    category: "Government / External", release_frequency: "Per semester",
    min_amount: 20000.00, max_amount: 20000.00, is_active: true
  }, {
    scholarship_id: 3, scholarship_code: "BPMSP",
    scholarship_name: "Bagong Pilipinas Merit Scholarship Program",
    description: "Merit-based scholarship by CHED, DepEd, and TESDA for academically talented Filipino students.",
    category: "Government / External", release_frequency: "Per semester",
    min_amount: 25000.00, max_amount: 25000.00, is_active: true
  });

  DB.scholarship_benefits.push({
    benefit_id: 3, scholarship_id: 2, benefit_name: "Semestral Stipend", benefit_type: "stipend",
    description: "Half Scholar: ₱20,000 per semester in total (₱17,500 stipend + ₱2,500 book/connectivity allowance), released every semester.", amount: 17500.00
  }, {
    benefit_id: 4, scholarship_id: 2, benefit_name: "Book & Connectivity Allowance", benefit_type: "other",
    description: "₱2,500 per semester, released together with the semestral stipend.", amount: null
  }, {
    benefit_id: 5, scholarship_id: 2, benefit_name: "Tuition and Other School Fees", benefit_type: "other",
    description: "Covered by Free Higher Education for SUC/LUC scholars.", amount: null
  }, {
    benefit_id: 6, scholarship_id: 3, benefit_name: "Merit Scholarship Benefits", benefit_type: "other",
    description: "Merit scholarship benefits per approved program configuration.", amount: null
  }, {
    benefit_id: 7, scholarship_id: 3, benefit_name: "Semestral Stipend", benefit_type: "stipend",
    description: "₱25,000 per semester, released every semester.", amount: 25000.00
  });

  DB.scholarship_requirements.push({
    requirement_id: 7, scholarship_id: 2, document_name: "Certificate of Registration",
    description: "Verifies that you are officially registered for the current academic term. This covers “Maintain active enrollment”.",
    where_to_get: "Registrar's Office / Student Portal",
    processing_time: "Usually available within 1–3 working days."
  }, {
    requirement_id: 8, scholarship_id: 2, document_name: "Certificate of Ratings (latest grade)",
    description: "Your grades from the last semester, uploaded every semester. This covers “Submit grades every semester” and “Maintain CHED-required GWA and academic standing” so the office can check that you meet the GWA CHED requires.",
    where_to_get: "Registrar's Office / Student Portal",
    processing_time: "Usually released after each term. A certified copy takes 3–5 working days."
  }, {
    requirement_id: 9, scholarship_id: 2, document_name: "Landbank Card",
    description: "Shows the Landbank account where your stipend and allowance are credited. The account name must match your registered name. Upload a clear photo of the front of your card.",
    where_to_get: "Landbank of the Philippines. Use the card you already have, or ask your branch or the scholarship office about a replacement.",
    processing_time: "Ready right away if you already have a card. A replacement can take a few working days."
  }, {
    requirement_id: 10, scholarship_id: 2, document_name: "Student ID",
    description: "Confirms your identity and that you are an enrolled DNSC student. Upload a clear photo or scan of the front and back.",
    where_to_get: "Your DNSC school ID, from the Registrar's Office or Student Affairs Office.",
    processing_time: "Ready right away if you have one. A replacement school ID can take 3–5 working days."
  }, {
    requirement_id: 11,
    scholarship_id: 3,
    document_name: "Certified true copy of COR/COE, or system-generated registration document certified by the Registrar (each succeeding payment)",
    description: "Proof that you are still actively enrolled in your degree program this term. Needed for each succeeding payment. This covers “Maintain active enrollment”.",
    where_to_get: "Registrar's Office / Student Portal",
    processing_time: "Usually issued within 1–3 working days."
  }, {
    requirement_id: 12,
    scholarship_id: 3,
    document_name: "Certified true copy of grades (GWA) from the previous semester/term (each succeeding payment)",
    description: "Your grades from the previous semester or term, needed for each succeeding payment. This covers “Submit grades every semester” and “Maintain required academic standing”.",
    where_to_get: "Registrar's Office / Student Portal",
    processing_time: "Usually released after each term. A certified copy takes 3–5 working days."
  }, {
    requirement_id: 13,
    scholarship_id: 3,
    document_name: "Certification of no other nationally funded scholarship (start of every academic year)",
    description: "A certification that you are not receiving any other nationally funded scholarship. Submit it at the start of every academic year.",
    where_to_get: "Registrar's Office or the Scholarship Office. Ask which office issues it at your school.",
    processing_time: "Processing time is not specified. Request it early."
  }, {
    requirement_id: 14,
    scholarship_id: 3,
    document_name: "Leave of Absence (Annex H) (if applicable)",
    description: "Only needed if you plan to take a leave of absence. Prior written approval is required, and a leave is allowed once for up to one academic year.",
    where_to_get: "CHED regional office, through the Scholarship Office",
    processing_time: "Not specified. Request approval before your leave starts."
  }, {
    requirement_id: 15,
    scholarship_id: 3,
    document_name: "Permit to Travel (if going abroad)",
    description: "Only needed if you are going abroad while you are a scholar.",
    where_to_get: "CHED regional office, through the Scholarship Office",
    processing_time: "Not specified. Request it before your trip."
  }, {
    requirement_id: 16,
    scholarship_id: 3,
    document_name: "Graduate exit form (Annex I) (at graduation)",
    description: "Submitted when you graduate to close out your scholarship.",
    where_to_get: "CHED regional office, through the Scholarship Office",
    processing_time: "Not specified. Submit it when you graduate."
  }, {
    requirement_id: 17,
    scholarship_id: 3,
    document_name: "Return Service Agreement papers (at graduation)",
    description: "Return service papers submitted when you graduate.",
    where_to_get: "CHED regional office, through the Scholarship Office",
    processing_time: "Not specified. Submit them when you graduate."
  });

  DB.scholarship_maintenance_requirements.push({
    maintenance_id: 7, scholarship_id: 2, category: "Academic Requirements", title: "Maintain CHED-required GWA and academic standing",
    description: "Keep your general weighted average (GWA) and academic standing at or above what CHED requires for merit scholars. Your grades are reviewed at the end of every semester, and falling below the requirement puts your scholarship under review."
  }, {
    maintenance_id: 8, scholarship_id: 2, category: "Compliance Requirements", title: "Submit grades every semester",
    description: "Upload your official grades as soon as your school releases them. Your stipend for the next term depends on it."
  }, {
    maintenance_id: 9, scholarship_id: 2, category: "Enrollment Requirements", title: "Maintain active enrollment",
    description: "Stay actively enrolled in your degree program each term."
  }, {
    maintenance_id: 10, scholarship_id: 3, category: "Academic Requirements", title: "Maintain required academic standing",
    description: "Keep your academic standing at or above what the Bagong Pilipinas Merit Scholarship Program requires. Your grades are reviewed at the end of every semester, and falling below the requirement puts your scholarship under review."
  }, {
    maintenance_id: 11, scholarship_id: 3, category: "Compliance Requirements", title: "Submit grades every semester",
    description: "Upload your official grades as soon as your school releases them. Your scholarship benefits for the next term depend on it."
  }, {
    maintenance_id: 12, scholarship_id: 3, category: "Enrollment Requirements", title: "Maintain active enrollment",
    description: "Stay actively enrolled in your degree program each term."
  });

  DB.scholars.push({
    scholar_id: 2, user_id: 2, scholar_number: "SCH-2026-00241", student_id: "2026-00241",
    first_name: "Karl Angelo", middle_name: "T.", last_name: "Gabaes",
    email: "karlangelo.gabaes@gmail.com", alternative_email: "kagabaes.dnsc@gmail.com",
    phone_number: "0926 481 7305", birth_date: "2005-11-02", age: 20, sex: "Male", nationality: "Filipino",
    home_address: "Purok 7, Mabini St., Brgy. Gredu", city: "Panabo City", province: "Davao del Norte", zip_code: "8105",
    campus_id: 1, scholar_status: "Active"
  }, {
    scholar_id: 3, user_id: 3, scholar_number: "SCH-2026-00387", student_id: "2026-00387",
    first_name: "Rochelle", middle_name: "D.", last_name: "Abella",
    email: "rochelle.abella@gmail.com", alternative_email: null,
    phone_number: "0948 205 6618", birth_date: "2008-05-19", age: 18, sex: "Female", nationality: "Filipino",
    home_address: "Purok 12, Aguinaldo St., Brgy. Apokon", city: "Tagum City", province: "Davao del Norte", zip_code: "8100",
    campus_id: 1, scholar_status: "Active"
  });

  DB.enrollments.push({
    enrollment_id: 2, scholar_id: 2, program_id: 2, term_id: 1, year_level: 2, gwa: null, renewal_status: "Pending"
  }, {
    enrollment_id: 3, scholar_id: 3, program_id: 3, term_id: 1, year_level: 1, gwa: null, renewal_status: "Pending"
  });

  DB.scholarship_awards.push({
    award_id: 2, scholar_id: 2, scholarship_id: 2, classification_id: null, award_status: "Approved", start_date: "2025-08-01"
  }, {
    award_id: 3, scholar_id: 3, scholarship_id: 3, classification_id: null, award_status: "Approved", start_date: "2026-08-01"
  });

  /* The two sample accounts are brand new: no submissions, no disbursements and
     no notifications yet. Files are uploaded by the scholar from the Documents page. */
  /* Signed-in scholar: the email saved by the sign-in page (scholarLogin).
     "scholarSession" is kept in sessionStorage, or in localStorage when
     "Remember me" is ticked. */
  function signedInEmail() {
    try {
      return (window.sessionStorage.getItem("scholarSession") ||
        window.localStorage.getItem("scholarSession") || "").toLowerCase();
    } catch (e) {
      return "";
    }
  }

  /* Clear the saved sign-in when the scholar confirms Log out. */
  document.addEventListener("click", function(e) {
    if (!e.target.closest || !e.target.closest("#logout-confirm")) return;
    try {
      window.sessionStorage.removeItem("scholarSession");
      window.localStorage.removeItem("scholarSession");
    } catch (err) {}
  });


  /* ---------- Helpers ---------- */
  function esc(v) {
    return String(v).replace(/[&<>"']/g, function(c) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;"
      } [c];
    });
  }

  function one(rows, key, val) {
    for (var i = 0; i < rows.length; i++)
      if (rows[i][key] === val) return rows[i];
    return null;
  }

  function toDate(s) {
    return new Date(String(s).replace(" ", "T"));
  }

  function fmtLong(s) {
    return toDate(s).toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
      year: "numeric"
    });
  }

  function fmtShort(s) {
    return toDate(s).toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric"
    });
  }

  function fmtMonthDay(s) {
    return toDate(s).toLocaleDateString("en-US", {
      month: "long",
      day: "numeric"
    });
  }

  function peso(n) {
    return "\u20B1" + Number(n).toLocaleString("en-PH", {
      maximumFractionDigits: 2
    });
  }

  function ordinal(n) {
    var s = ["th", "st", "nd", "rd"],
      v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
  }

  function relTime(s) {
    var diff = Date.now() - toDate(s).getTime();
    var min = Math.floor(diff / 60000);
    if (min >= 0 && min < 60) return min <= 1 ? "Just now" : min + " minutes ago";
    var hr = Math.floor(min / 60);
    if (min >= 0 && hr < 24) return hr + (hr === 1 ? " hour ago" : " hours ago");
    var day = Math.floor(hr / 24);
    if (min >= 0 && day === 1) return "Yesterday";
    if (min >= 0 && day < 7) return day + " days ago";
    return fmtLong(s);
  }

  function pillFor(label) {
    var l = String(label).toLowerCase();
    if (/^(approved|active|released|verified|completed)/.test(l)) return {
      cls: "pill-active",
      icon: "\u2713"
    };
    if (/^(scheduled|processing|under|pending)/.test(l)) return {
      cls: "pill-amber",
      icon: "\u25F7"
    };
    if (/(rejected|revoked|suspended|terminated|failed|hold|inactive)/.test(l)) return {
      cls: "pill-danger",
      icon: "!"
    };
    return {
      cls: "pill-pending",
      icon: "\u2022"
    };
  }

  /* One rule for missing data: a null, empty or "N/A" value is replaced by
     a plain phrase, never left blank. */
  function blank(v) {
    return v === null || v === undefined || String(v).trim() === "" || String(v).trim().toUpperCase() === "N/A";
  }

  function orNone(v, phrase) {
    return blank(v) ? phrase : v;
  }

  function dateOr(s, phrase) {
    return blank(s) ? phrase : fmtLong(s);
  }

  /* The same stipend text everywhere: amount + scholarships.release_frequency. */
  var FREQ_SUFFIX = {
    "per month": " / month",
    "per semester": " / semester",
    "per academic year": " / year"
  };

  function stipendText(b) {
    if (!b || blank(b.amount)) return "Amount not stated";
    var f = scholarship ? String(scholarship.release_frequency || "").toLowerCase() : "";
    return peso(b.amount) + (FREQ_SUFFIX[f] || "");
  }

  function setText(id, text) {
    var el = document.getElementById(id);
    if (el) el.textContent = text;
  }

  function setHtml(id, html) {
    var el = document.getElementById(id);
    if (el) el.innerHTML = html;
  }

  function setPill(id, label) {
    var el = document.getElementById(id);
    if (!el) return;
    var p = pillFor(label);
    el.className = "status-pill " + p.cls;
    el.innerHTML = '<i aria-hidden="true">' + p.icon + "</i> " + esc(label);
  }

  /* ---------- Current scholar, term, award ---------- */
  var scholar = one(DB.scholars, "email", signedInEmail()) || DB.scholars[0];
  var term = DB.academic_terms.filter(function(t) {
    return t.is_current;
  })[0];
  var campus = one(DB.campuses, "campus_id", scholar.campus_id);
  var enrollment = DB.enrollments.filter(function(e) {
    return e.scholar_id === scholar.scholar_id && e.term_id === term.term_id;
  })[0];
  var program = one(DB.academic_programs, "program_id", enrollment.program_id);
  var award = DB.scholarship_awards.filter(function(a) {
    return a.scholar_id === scholar.scholar_id;
  })[0];
  var scholarship = one(DB.scholarships, "scholarship_id", award.scholarship_id);
  var benefits = DB.scholarship_benefits.filter(function(b) {
    return b.scholarship_id === scholarship.scholarship_id;
  });
  var maintenance = DB.scholarship_maintenance_requirements.filter(function(m) {
    return m.scholarship_id === scholarship.scholarship_id;
  });
  var notifs = DB.notifications.filter(function(n) {
    return n.audience_role === "scholar" && n.scholar_id === scholar.scholar_id;
  });
  var fullName = [scholar.first_name, scholar.middle_name, scholar.last_name].filter(Boolean).join(" ");
  var initials = (scholar.first_name.charAt(0) + scholar.last_name.charAt(0)).toUpperCase();

  /* One entry per requirement. The latest version of its submission (if
     any) gives the file and verification_status. */
  var docs = DB.scholarship_requirements.filter(function(r) {
    return r.scholarship_id === scholarship.scholarship_id;
  }).map(function(r) {
    var rows = DB.requirement_submissions.filter(function(s) {
      return s.award_id === award.award_id && s.requirement_id === r.requirement_id && s.term_id === term.term_id;
    }).sort(function(a, b) {
      return b.version_no - a.version_no;
    });
    var s = rows[0] || null;
    var status = "pending";
    if (s && s.verification_status === "verified") status = "verified";
    if (s && s.verification_status === "rejected") status = "resubmission";
    var base = s ? s.file_path.split("/").pop() : "";
    var m = /\.([^.]+)$/.exec(base);
    return {
      id: String(r.requirement_id),
      name: r.document_name,
      description: orNone(r.description, "No description provided."),
      whereToGet: orNone(r.where_to_get, "Not specified"),
      processingTime: orNone(r.processing_time, "Not specified"),
      deadline: dateOr(term.document_deadline, "No deadline set"),
      status: status,
      reviewerNote: s ? s.reviewer_note : null,
      submission: s ? {
        fileName: base,
        fileType: m ? m[1].toUpperCase() : "",
        submittedOn: fmtLong(s.submitted_at)
      } : null
    };
  });

  /* Display state of a document: pending | review | rejected | submitted */
  function docState(d) {
    if (d.status === "resubmission") return "rejected";
    if (d.status === "verified") return "submitted";
    if (d.submission) return "review";
    return "pending";
  }
  var STATE = {
    pending: {
      label: "Pending",
      pill: "pill-pending",
      i: "\u2022",
      path: '<circle cx="12" cy="12" r="9" /><path d="M12 8v4.5M12 16h.01" />'
    },
    review: {
      label: "Under Verification",
      pill: "pill-amber",
      i: "\u25F7",
      path: '<circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />'
    },
    rejected: {
      label: "Revision Needed",
      pill: "pill-danger",
      i: "!",
      path: '<path d="M18 6 6 18M6 6l12 12" />'
    },
    submitted: {
      label: "Approved",
      pill: "pill-active",
      i: "\u2713",
      path: '<path d="M20 6 9 17l-5-5" />'
    }
  };

  function docRow(d, withMeta) {
    var st = docState(d),
      S = STATE[st];
    var meta = "";
    if (withMeta) {
      if (st === "rejected" && d.reviewerNote) meta = '<div class="doc-meta">' + esc(d.reviewerNote) + "</div>";
      else if (d.submission && st !== "rejected") meta = '<div class="doc-meta">Received ' + esc(d.submission.submittedOn) + "</div>";
    }
    var btn = "";
    if (st === "pending") btn = '<button type="button" class="doc-btn btn primary sm" data-view="documents">Upload File</button>';
    if (st === "rejected") btn = '<button type="button" class="doc-btn btn primary sm" data-view="documents">Re-upload File</button>';
    var action = st === "pending" || st === "rejected" ? " action" : "";
    return (
      '<li class="doc-row vault-doc' + action + " " + st + '">' +
      '<span class="doc-icon ' + st + '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">' + S.path + "</svg></span>" +
      '<div class="doc-text"><div class="doc-name">' + esc(d.name) + "</div>" + meta + "</div>" +
      '<span class="status-pill ' + S.pill + '"><i aria-hidden="true">' + S.i + "</i> " + S.label + "</span>" + btn +
      "</li>"
    );
  }

  function actionDocs() {
    var p = docs.filter(function(d) {
      return docState(d) === "pending";
    });
    var r = docs.filter(function(d) {
      return docState(d) === "rejected";
    });
    return p.concat(r);
  }

  /* ---------- Documents summary (dashboard + My Scholarship) ---------- */
  function renderDocs() {
    var total = docs.length;
    var count = {
      pending: 0,
      review: 0,
      rejected: 0,
      submitted: 0
    };
    docs.forEach(function(d) {
      count[docState(d)]++;
    });
    var sent = count.review + count.submitted;
    var action = count.pending + count.rejected;
    var pct = total ? Math.round(sent / total * 100) : 0;

    /* Dashboard hero ring */
    var ringFill = document.getElementById("sd-ring-fill");
    if (ringFill) ringFill.style.setProperty("--ring-offset", String(326.7 * (1 - (total ? sent / total : 0))));
    setText("sd-ring-pct", pct + "%");
    setText("sd-ring-caption", total ? sent + " of " + total + " requirements complete" : "No required documents yet");
    var ring = document.getElementById("sd-hero-ring");
    if (ring) ring.setAttribute("aria-label", "Requirements progress: " + sent + " of " + total + " complete");

    /* Dashboard: needs your action */
    var acts = actionDocs();
    /* Dashboard preview: more than 4 documents needing action shows only the first 3 (full list stays in My Scholarship / Documents). */
    var dashActs = acts.length > 4 ? acts.slice(0, 3) : acts;
    setHtml("sd-action-list", dashActs.length ? dashActs.map(function(d) {
      return docRow(d, false);
    }).join("") : '<li class="ben-empty">' + (total ? "Nothing needs your action right now." : "No documents are required yet.") + "</li>");

    /* Dashboard: snapshot readiness */
    setHtml("sd-snap-ready", total ? "<strong>" + sent + " of " + total + "</strong> documents submitted" : "No required documents");

    /* My Scholarship: summary card */
    setText("sd-sum-fraction", sent + "/" + total);
    var cp = document.getElementById("sd-compact");
    if (cp) cp.setAttribute("aria-label", sent + " of " + total + " documents submitted");
    setHtml("sd-docs-progress", total ? sent + " of " + total + " Documents Submitted" +
      (count.review ? "<span>Includes " + count.review + " under review</span>" : "") : "No required documents yet");
    var al = document.getElementById("sd-docs-alert");
    if (al) {
      al.classList.toggle("clear", action === 0);
      al.innerHTML = '<span class="req-dot">' + (action === 0 ? "\u2713" : "!") + "</span> " +
        (action === 0 ? (total ? "Nothing needs your action right now" : "No documents are required yet") :
          action + (action === 1 ? " document needs" : " documents need") + " your action" +
          (blank(term.document_deadline) ? "" : " by " + esc(fmtLong(term.document_deadline))));
    }
    setText("sd-n-submitted", count.submitted);
    setText("sd-n-pending", count.pending);
    setText("sd-n-review", count.review);
    setText("sd-n-rejected", count.rejected);
    setText("sd-n-total", total);

    /* My Scholarship: grouped list */
    function group(id, cls, label, items, meta) {
      if (!items.length) return "";
      return (
        '<section class="doc-group" aria-labelledby="' + id + '">' +
        '<h3 class="doc-group-label ' + cls + '" id="' + id + '">' + label +
        '<span class="doc-group-count">' + items.length + "</span></h3>" +
        '<ul class="doc-list">' + items.map(function(d) {
          return docRow(d, true);
        }).join("") + "</ul></section>"
      );
    }
    var rev = docs.filter(function(d) {
      return docState(d) === "review";
    });
    var done = docs.filter(function(d) {
      return docState(d) === "submitted";
    });
    setHtml("sd-doc-groups", (total ? "" : '<p class="ben-empty">No required documents have been listed for this scholarship yet.</p>') +
      group("g-action", "action", "Action Needed", acts) +
      group("g-review", "review", "Under Review", rev) +
      group("g-done", "done", "Completed", done) +
      '<a class="card-link" href="#documents" data-view="documents">Manage documents</a>');
  }

  /* ---------- Notifications ---------- */
  var NOTIF = {
    document_deadline: {
      label: "View Document",
      primary: false,
      view: "documents",
      icon: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />'
    },
    resubmission: {
      label: "Resubmit",
      primary: true,
      view: "documents",
      icon: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />'
    },
    stipend: {
      label: "View Stipend",
      primary: false,
      view: "benefits",
      icon: '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />'
    }
  };
  var CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>';

  function notifHtml(n, full) {
    var t = NOTIF[n.notification_type] || NOTIF.document_deadline;
    var unread = !n.read_at;
    var warn = unread && n.notification_type !== "stipend" ? " warn" : "";
    var body = '<div class="notif-msg">' + esc(orNone(n.title, "Notification")) + "</div>";
    if (full) {
      if (n.message) body += '<div class="notif-sub' + warn + '">' + esc(n.message) + "</div>";
      body += '<div class="notif-time">' + esc(dateOr(n.created_at, "Date not available")) + "</div>";
      body += '<button type="button" class="doc-btn btn ' + (t.primary ? "primary" : "quiet") + ' sm notif-action" data-view="' + t.view + '">' + t.label + "</button>";
    } else {
      body += '<div class="notif-time">' + esc(blank(n.created_at) ? "Date not available" : relTime(n.created_at)) + "</div>";
    }
    return (
      '<li class="notif-item' + (unread ? " unread" : "") + '" data-notif-id="' + n.notification_id + '">' +
      (unread ? '<span class="sr-only">Unread</span>' : "") +
      '<div class="notif-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' + t.icon + "</svg></div>" +
      '<div class="notif-text">' + body + "</div>" +
      '<div class="notif-dot' + (unread ? "" : " read") + '"></div>' +
      (unread ? '<button type="button" class="mark-read-btn" aria-label="Mark notification as read" title="Mark notification as read">' + CHECK + "</button>" : "") +
      "</li>"
    );
  }

  function renderNotifs() {
    var sorted = notifs.slice().sort(function(a, b) {
      return toDate(b.created_at) - toDate(a.created_at);
    });
    var unread = sorted.filter(function(n) {
      return !n.read_at;
    }).length;
    var label = !sorted.length ? "No notifications yet" : unread ? unread + " unread" : "All caught up";
    var EMPTY_N = '<li class="ben-empty">No notifications yet.</li>';
    setHtml("sd-recent-notifs", sorted.length ? sorted.slice(0, 4).map(function(n) {
      return notifHtml(n, false);
    }).join("") : EMPTY_N);
    setText("sd-recent-sub", sorted.length ? label : "No new notifications");
    setHtml("notif-full-list", sorted.length ? sorted.map(function(n) {
      return notifHtml(n, true);
    }).join("") : EMPTY_N);
    setText("notif-unread-count", label);
  }

  /* ---------- Static-slot rendering ---------- */
  function fields(rows) {
    return rows.map(function(r) {
      return '<div class="profile-field"><span class="profile-field-label">' + esc(r[0]) +
        '</span><span class="profile-field-value">' + esc(orNone(r[1], "Not provided")) + "</span></div>";
    }).join("");
  }

  function renderPage() {
    var yearLabel = blank(enrollment.year_level) ? "Year level not set" : ordinal(enrollment.year_level) + " Year";
    var ay = blank(term.academic_year) ? "Academic year not set" : "Academic Year " + term.academic_year;
    var programName = orNone(program && program.program_name, "Program not listed");
    var campusName = orNone(campus && campus.campus_name, "Campus not listed");

    /* Sidebar */
    setText("sd-side-avatar", initials);
    setText("sd-side-name", fullName);
    setText("sd-side-meta", programName);
    setText("sd-side-year", yearLabel);

    /* Dashboard */
    setText("sd-first-name", scholar.first_name);
    setPill("sd-hero-status", orNone(award.award_status, "Status not available"));
    setText("sd-hero-title", orNone(scholarship.scholarship_name, "No scholarship assigned"));
    setText("sd-hero-sub", ay);
    setText("sd-hero-renewal-text", blank(enrollment.renewal_status) ? "Renewal status not available" : "Renewal status: " + enrollment.renewal_status);
    setPill("sd-snap-standing", orNone(scholar.scholar_status, "Status not available"));
    var renewIcon = document.querySelector("#view-dashboard .hero-renewal svg");
    if (renewIcon) renewIcon.style.display = /^eligible/i.test(String(enrollment.renewal_status || "")) ? "" : "none";
    var stipend = benefits.filter(function(b) {
      return String(b.benefit_type).toLowerCase() === "stipend";
    })[0];
    var snapStipend = document.getElementById("sd-snap-stipend-row");
    if (snapStipend) {
      setText("sd-snap-stipend-label", stipend ? stipend.benefit_name : "Stipend");
      setText("sd-snap-stipend", stipend ? stipendText(stipend) : "No stipend for this scholarship");
    }

    /* My Scholarship overview */
    setText("sd-ov-title", orNone(scholarship.scholarship_name, "No scholarship assigned"));
    setText("sd-ov-sub", ay);
    setPill("sd-ov-status", orNone(award.award_status, "Status not available"));
    var released = DB.disbursements.filter(function(d) {
      return d.award_id === award.award_id && d.release_date;
    }).sort(function(a, b) {
      return toDate(b.release_date) - toDate(a.release_date);
    })[0];
    setText("sd-ov-disbursed", released ? peso(released.amount) + (released.release_period ? " \u00B7 " + released.release_period : "") : "No disbursement yet");
    setText("sd-ov-term", blank(term.academic_year) ? "Not set" : "AY " + term.academic_year);
    setText("sd-ov-deadline", dateOr(term.document_deadline, "No deadline set"));

    /* Maintaining requirements, grouped by category */
    var order = [],
      byCat = {};
    maintenance.forEach(function(m) {
      if (!byCat[m.category]) {
        byCat[m.category] = [];
        order.push(m.category);
      }
      byCat[m.category].push(m);
    });
    setHtml("sd-maint-groups", !order.length ? '<p class="ben-empty">No maintaining requirements are listed for this scholarship.</p>' : order.map(function(cat) {
      return (
        '<div class="acc-group"><div class="acc-group-head">' +
        '<span class="acc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 3 7v6c0 5 4 8.5 9 9 5-.5 9-4 9-9V7z" /><path d="m9 12 2 2 4-4" /></svg></span>' +
        '<span class="acc-group-title">' + esc(orNone(cat, "Other Requirements")) + "</span></div>" +
        '<div class="acc-group-body">' + byCat[cat].map(function(m) {
          return '<div class="acc-req"><div class="acc-req-title">' + esc(orNone(m.title, "Requirement")) + "</div><p>" + esc(orNone(m.description, "No description provided.")) + "</p></div>";
        }).join("") + "</div></div>"
      );
    }).join(""));

    /* Profile */
    setText("pf-avatar", initials);
    setText("profile-hero-name", fullName);
    setText("pf-meta", programName + " \u00B7 " + campusName);
    setText("pf-year", yearLabel);
    setText("pf-scholar-no", orNone(scholar.scholar_number, "Not assigned"));
    setText("pf-scholarship", orNone(scholarship.scholarship_name, "No scholarship assigned"));
    setHtml("pf-personal", fields([
      ["Name", fullName],
      ["Sex", scholar.sex],
      ["Birthday", dateOr(scholar.birth_date, "Not provided")],
      ["Nationality", scholar.nationality]
    ]));
    setHtml("pf-contact", fields([
      ["Gmail", scholar.email],
      ["Alternative Gmail", scholar.alternative_email],
      ["Mobile Number", scholar.phone_number]
    ]));
    setHtml("pf-school", fields([
      ["Course", programName],
      ["Year", yearLabel]
    ]));
    setHtml("pf-address", fields([
      ["Specific Address", scholar.home_address],
      ["City", scholar.city],
      ["Province", scholar.province],
      ["ZIP Code", scholar.zip_code]
    ]));

    renderDocs();
    renderNotifs();
  }

  /* Shared with the Documents and Benefits scripts below. */
  window.SD = {
    db: DB,
    scholarship: scholarship,
    term: term,
    docs: docs,
    userKey: scholar.email,
    benefits: benefits,
    stipendText: stipendText,
    orNone: orNone,
    refreshDocs: renderDocs
  };

  renderPage();
})();

/* ---------- next block ---------- */
(function() {
  // Profile and Settings navigation
  // (added to the same views/hashes maps the rest of the sidebar already
  // uses, so the existing show()/fromHash() logic below handles them
  // automatically: only one nav-item is ever active at a time.)
  var views = {
    dashboard: document.getElementById("view-dashboard"),
    scholarship: document.getElementById("view-scholarship"),
    documents: document.getElementById("view-documents"),
    benefits: document.getElementById("view-benefits"),
    notifications: document.getElementById("view-notifications"),
    profile: document.getElementById("view-profile"),
    settings: document.getElementById("view-settings"),
  };
  var hashes = {
    dashboard: "#dashboard",
    scholarship: "#my-scholarship",
    documents: "#documents",
    benefits: "#benefits",
    notifications: "#notifications",
    profile: "#profile",
    settings: "#settings",
  };
  var triggers = document.querySelectorAll("[data-view]");
  var navItems = document.querySelectorAll(".nav-item[data-view]");

  // Mobile burger menu: opens/closes the existing sidebar as an
  // off-canvas panel at 768px and below. Reuses the sidebar markup
  // and the show()/go() view-switching logic below as-is.
  var menuToggle = document.getElementById("menu-toggle");
  var sidebarEl = document.getElementById("sidebar");
  var sidebarBackdrop = document.getElementById("sidebar-backdrop");
  var sidebarCloseBtn = document.getElementById("sidebar-close");

  function openSidebarMenu() {
    if (!sidebarEl) return;
    sidebarEl.classList.add("open");
    sidebarBackdrop.classList.add("open");
    menuToggle.setAttribute("aria-expanded", "true");
    menuToggle.setAttribute("aria-label", "Close navigation menu");
    document.body.classList.add("sidebar-open");
  }

  function closeSidebarMenu() {
    if (!sidebarEl || !sidebarEl.classList.contains("open")) return;
    sidebarEl.classList.remove("open");
    sidebarBackdrop.classList.remove("open");
    menuToggle.setAttribute("aria-expanded", "false");
    menuToggle.setAttribute("aria-label", "Open navigation menu");
    document.body.classList.remove("sidebar-open");
  }
  if (menuToggle && sidebarEl && sidebarBackdrop && sidebarCloseBtn) {
    menuToggle.addEventListener("click", function() {
      if (sidebarEl.classList.contains("open")) closeSidebarMenu();
      else openSidebarMenu();
    });
    sidebarCloseBtn.addEventListener("click", closeSidebarMenu);
    sidebarBackdrop.addEventListener("click", closeSidebarMenu);
    document.addEventListener("keydown", function(e) {
      if (e.key === "Escape") closeSidebarMenu();
    });
  }

  function show(name) {
    if (!views[name]) return;
    Object.keys(views).forEach(function(key) {
      views[key].hidden = key !== name;
      views[key].classList.remove("view-enter");
    });
    // Force a fresh animation on every sidebar/content navigation click.
    void views[name].offsetWidth;
    views[name].classList.add("view-enter");
    navItems.forEach(function(item) {
      var on = item.getAttribute("data-view") === name;
      item.classList.toggle("active", on);
      if (on) item.setAttribute("aria-current", "page");
      else item.removeAttribute("aria-current");
    });
  }

  function fromHash() {
    var match = "dashboard";
    Object.keys(hashes).forEach(function(key) {
      if (hashes[key] === location.hash) match = key;
    });
    show(match);
  }

  /* Delegated so buttons that are (re-)rendered by JS, such as the
     dashboard's "Upload File" / "Re-upload File" actions, still navigate. */
  document.addEventListener("click", function(e) {
    var el = e.target.closest("[data-view]");
    if (!el) return;
    var name = el.getAttribute("data-view");
    if (!views[name]) return;
    e.preventDefault();
    show(name);
    history.replaceState(null, "", hashes[name]);
    window.scrollTo(0, 0);
    closeSidebarMenu(); // no-op on desktop / when already closed
  });
  window.addEventListener("hashchange", fromHash);
  fromHash();

  /* Accordions: every item toggles independently, all closed by default */
  document.addEventListener("click", function(e) {
    var btn = e.target.closest(".acc-trigger");
    if (!btn) return;
    var item = btn.closest(".acc-item");
    var open = !item.classList.contains("open");
    item.classList.toggle("open", open);
    btn.setAttribute("aria-expanded", String(open));
  });

  /* Card-level collapsibles (Maintaining Requirements): the whole card toggles as one unit, showing
     every item's title and description when expanded. */
  document.addEventListener("click", function(e) {
    var btn = e.target.closest(".card-head-btn");
    if (!btn) return;
    var card = btn.closest(".collapsible-card");
    if (!card) return;
    var open = card.classList.contains("collapsed");
    card.classList.toggle("collapsed", !open);
    btn.setAttribute("aria-expanded", String(open));
  });

  document.addEventListener("click", function(e) {
    var readBtn = e.target.closest(".mark-read-btn");
    if (!readBtn) return;
    var item = readBtn.closest(".notif-item");
    if (!item) return;
    /* The same notification (data-notif-id) can appear on the dashboard and
       on the Notifications page: mark every copy as read. */
    var nid = item.getAttribute("data-notif-id");
    var copies = nid ? document.querySelectorAll('.notif-item[data-notif-id="' + nid + '"]') : [item];
    copies.forEach(function(copy) {
      copy.classList.remove("unread");
      var cdot = copy.querySelector(".notif-dot");
      if (cdot) cdot.classList.add("read");
      var cbtn = copy.querySelector(".mark-read-btn");
      if (cbtn) {
        cbtn.setAttribute("aria-label", "Notification marked as read");
        cbtn.setAttribute("title", "Notification marked as read");
        cbtn.disabled = true;
      }
    });
    var seen = {};
    document.querySelectorAll(".notif-item.unread").forEach(function(n) {
      seen[n.getAttribute("data-notif-id") || Math.random()] = true;
    });
    var unread = Object.keys(seen).length;
    document.querySelectorAll(".notif-panel .card-sub").forEach(function(label) {
      if (/unread|caught up/i.test(label.textContent)) label.textContent = unread ? unread + " unread" : "All caught up";
    });
  });

  /* Notifications: clicking an unread item (its body, not an action
     button) marks it read and updates the unread count. Action buttons
     keep their own data-view navigation handled above. */
  var notifList = document.getElementById("notif-full-list");
  if (notifList) {
    function updateUnreadCount() {
      var n = notifList.querySelectorAll(".notif-item.unread").length;
      var total = notifList.querySelectorAll(".notif-item").length;
      var label = document.getElementById("notif-unread-count");
      if (label) label.textContent = total === 0 ? "No notifications yet" : n === 0 ? "All caught up" : n + " unread";
    }

    function markRead(item) {
      if (!item.classList.contains("unread")) return;
      item.classList.remove("unread");
      var dot = item.querySelector(".notif-dot");
      if (dot) dot.classList.add("read");
      updateUnreadCount();
    }
    notifList.addEventListener("click", function(e) {
      var item = e.target.closest(".notif-item");
      if (!item) return;
      markRead(item);
    });
    updateUnreadCount();
  }

  /* Greeting, date and time follow the visitor's clock on both pages */
  function setAll(sel, text) {
    document.querySelectorAll(sel).forEach(function(n) {
      n.textContent = text;
    });
  }

  function tick() {
    var now = new Date();
    var h = now.getHours();
    setAll(".js-greeting", h < 12 ? "Good morning" : h < 18 ? "Good afternoon" : "Good evening");
    setAll(
      ".js-date",
      now.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric"
      }).toUpperCase()
    );
    setAll(
      ".js-time",
      now.toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit"
      }).replace(/\s/g, "")
    );
  }
  tick();
  setInterval(tick, 60000);

  // Profile and Settings navigation
  // Edit Password: simple frontend-only validation (no backend here).
  var passwordForm = document.getElementById("password-form");
  if (passwordForm) {
    var currentPw = document.getElementById("current-password");
    var newPw = document.getElementById("new-password");
    var confirmPw = document.getElementById("confirm-password");
    var pwMsg = document.getElementById("password-msg");

    var pwToggle = document.getElementById("password-toggle");
    var pwCancel = document.getElementById("password-cancel");

    function showPasswordMsg(kind, text) {
      pwMsg.className = "form-msg " + kind;
      pwMsg.textContent = text;
      pwMsg.hidden = false;
    }

    // The form is optional: it stays hidden until the scholar asks for it.
    function setPasswordOpen(open) {
      passwordForm.hidden = !open;
      pwToggle.hidden = open;
      pwToggle.setAttribute("aria-expanded", String(open));
      if (open) {
        pwMsg.hidden = true;
        currentPw.focus();
      } else {
        passwordForm.reset();
        pwToggle.focus();
      }
    }
    pwToggle.addEventListener("click", function() {
      setPasswordOpen(true);
    });
    pwCancel.addEventListener("click", function() {
      pwMsg.hidden = true;
      setPasswordOpen(false);
    });

    passwordForm.addEventListener("submit", function(e) {
      e.preventDefault();
      if (!currentPw.value || !newPw.value || !confirmPw.value) {
        showPasswordMsg("error", "Please fill in all three password fields.");
        return;
      }
      if (newPw.value.length < 8) {
        showPasswordMsg("error", "New password must be at least 8 characters long.");
        return;
      }
      if (newPw.value !== confirmPw.value) {
        showPasswordMsg("error", "New password and confirmation do not match.");
        return;
      }
      // No backend yet: just confirm the change, clear and hide the form.
      setPasswordOpen(false);
      showPasswordMsg("success", "Your password has been updated.");
    });
  }

  // Logout: shows a confirmation modal instead of a real sign-out.
  var logoutOpenBtn = document.getElementById("logout-open");
  var logoutBackdrop = document.getElementById("logout-backdrop");
  if (logoutOpenBtn && logoutBackdrop) {
    var logoutCancelBtn = document.getElementById("logout-cancel");
    var logoutCloseBtn = document.getElementById("logout-close");
    var logoutConfirmBtn = document.getElementById("logout-confirm");

    function openLogoutModal() {
      logoutBackdrop.hidden = false;
      document.body.classList.add("modal-open");
      logoutConfirmBtn.focus();
    }

    function closeLogoutModal() {
      logoutBackdrop.hidden = true;
      document.body.classList.remove("modal-open");
      logoutOpenBtn.focus();
    }

    logoutOpenBtn.addEventListener("click", openLogoutModal);
    logoutCancelBtn.addEventListener("click", closeLogoutModal);
    logoutCloseBtn.addEventListener("click", closeLogoutModal);
    logoutBackdrop.addEventListener("click", function(e) {
      if (e.target === logoutBackdrop) closeLogoutModal();
    });
    document.addEventListener("keydown", function(e) {
      if (e.key === "Escape" && !logoutBackdrop.hidden) closeLogoutModal();
    });
    var loggingOut = false;
    logoutConfirmBtn.addEventListener("click", function x(e) {
      // Hold the link's instant jump, show the preloader, then go to the login page.
      e.preventDefault();
      if (loggingOut) return;
      loggingOut = true;
      var link = logoutConfirmBtn.querySelector("a");
      var target = link ? link.href : null;
      // No real auth session here: just confirm and return to Dashboard.
      closeLogoutModal();
      show("dashboard");
      history.replaceState(null, "", hashes.dashboard);

      var preloader = document.getElementById("nxPreloader");
      if (preloader) {
        preloader.classList.remove("nx-preloader-hide");
        preloader.setAttribute("aria-hidden", "false");
      }
      if (target) {
        setTimeout(function() {
          window.location.href = target;
        }, 1600);
      }
    });
  }
})();

/* ---------- next block ---------- */
/* =====================================================================
     DOCUMENTS: data + interactions (vanilla JS, no backend).
     Requirements are keyed by scholarship, so another scholarship can
     ship its own list. Only the scholar's current scholarship is shown.
     ===================================================================== */
(function() {
  "use strict";

  /* ---------- Data ----------
     Comes from the shared database object above: each requirement is a
     scholarship_requirements row joined with its latest
     requirement_submissions row. */
  var SD = window.SD;

  function currentScholarship() {
    return {
      name: SD.scholarship.scholarship_name,
      academicYear: "Academic Year " + SD.term.academic_year,
      documents: SD.docs
    };
  }

  function findDoc(id) {
    var docs = currentScholarship().documents;
    for (var i = 0; i < docs.length; i++)
      if (docs[i].id === id) return docs[i];
    return null;
  }

  /* ---------- Helpers ---------- */
  var ICONS = {
    file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6M8 13h8M8 17h5" />',
    grades: '<path d="M22 10 12 5 2 10l10 5 10-5Z" /><path d="M6 12.5V17c0 1.5 3 3 6 3s6-1.5 6-3v-4.5" />',
    enrollment: '<path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2z" /><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z" />',
    id: '<rect x="3" y="5" width="18" height="14" rx="2" /><circle cx="9" cy="11" r="2" /><path d="M14 10h4M14 14h4M6 16c.5-1.3 1.6-2 3-2s2.5.7 3 2" />',
    agreement: '<path d="M12 2 3 7v6c0 5 4 8.5 9 9 5-.5 9-4 9-9V7z" /><path d="m9 12 2 2 4-4" />',
    conduct: '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z" />'
  };
  var ALLOWED = ["pdf", "jpg", "jpeg", "png", "doc", "docx"];
  var MAX_BYTES = 10 * 1024 * 1024;
  var RING_C = 238.76;

  function esc(v) {
    return String(v).replace(/[&<>"']/g, function(c) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;"
      } [c];
    });
  }

  function fmtSize(bytes) {
    if (bytes < 1024 * 1024) return Math.max(1, Math.round(bytes / 1024)) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
  }

  function fileExt(name) {
    var m = /\.([^.]+)$/.exec(name);
    return m ? m[1].toLowerCase() : "";
  }

  function fmtDate(d) {
    return d.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
      year: "numeric"
    });
  }

  function statusInfo(d) {
    if (d.status === "verified")
      return {
        label: "Approved",
        cls: "pill-active",
        icon: "✓",
        note: "Checked and accepted by the scholarship officer."
      };
    if (d.status === "resubmission")
      return {
        label: "Revision Needed",
        cls: "pill-danger",
        icon: "!",
        note: "Upload a corrected copy."
      };
    if (d.submission)
      return {
        label: "Under Verification",
        cls: "pill-amber",
        icon: "◷",
        note: "Waiting for the scholarship officer to verify it."
      };
    return {
      label: "Pending",
      cls: "pill-pending",
      icon: "•",
      note: "Not uploaded yet."
    };
  }

  function actionInfo(d) {
    if (d.status === "verified") return {
      short: "View / Replace",
      long: "View / Replace",
      primary: false
    };
    if (d.status === "resubmission") return {
      short: "Replace",
      long: "Replace Document",
      primary: true
    };
    if (d.submission) return {
      short: "Replace",
      long: "Replace Document",
      primary: false
    };
    return {
      short: "Upload",
      long: "Upload Document",
      primary: true
    };
  }

  function needsAction(d) {
    return d.status === "resubmission" || (d.status === "pending" && !d.submission);
  }

  /* ---------- Rendering ---------- */
  var list = document.getElementById("docs-acc");

  function actionBtn(d, size) {
    var a = actionInfo(d);
    var label = size === "long" ? a.long : a.short;
    var icon =
      a.primary ?
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="m17 8-5-5-5 5M12 3v12" /></svg>' :
      "";
    return (
      '<button type="button" class="doc-btn doc-action ' + (a.primary ? "primary" : "quiet") + '"' +
      ' data-doc-id="' + esc(d.id) + '" aria-label="' + esc(a.long + ": " + d.name) + '">' +
      icon + esc(label) + "</button>"
    );
  }

  function fileRow(d) {
    var f = d.submission;
    var iconSvg =
      '<span class="doc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /></svg></span>';
    if (!f) {
      return (
        '<div class="doc-file">' + iconSvg + "<div>" +
        '<div class="doc-file-name">No file uploaded yet</div>' +
        '<div class="doc-file-meta">Accepted formats: PDF, JPG, PNG, DOC, DOCX.</div>' +
        "</div></div>"
      );
    }
    var name = f.url ?
      '<button type="button" class="doc-link" data-preview="' + esc(d.id) + '">' + esc(f.fileName) + "</button>" :
      esc(f.fileName);
    return (
      '<div class="doc-file">' + iconSvg + "<div>" +
      '<div class="doc-file-name">' + name + "</div>" +
      '<div class="doc-file-meta">' + esc(f.fileType) + " file, submitted " + esc(f.submittedOn) +
      (f.url ? ' \u00B7 <button type="button" class="doc-link" data-preview="' + esc(d.id) + '">Preview</button>' : "") + "</div>" +
      "</div></div>"
    );
  }

  function itemHtml(d) {
    var s = statusInfo(d);
    var bid = "doc-" + d.id + "-btn";
    var pid = "doc-" + d.id + "-panel";
    var remark =
      d.status === "resubmission" && d.reviewerNote ?
      '<div class="doc-remark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="9" /><path d="M12 8v4.5M12 16h.01" /></svg><span><strong>Officer’s note:</strong> ' + esc(d.reviewerNote) + "</span></div>" :
      "";
    return (
      '<li class="acc-item vault-doc" data-doc-id="' + esc(d.id) + '">' +
      '<div class="doc-head">' +
      '<button type="button" class="acc-trigger" id="' + bid + '" aria-expanded="false" aria-controls="' + pid + '">' +
      '<span class="acc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' + ICONS.file + "</svg></span>" +
      '<span class="acc-title"><span class="doc-title-name">' + esc(d.name) + '</span><span class="doc-deadline">Deadline: ' + esc(d.deadline) + "</span></span>" +
      "</button>" +
      '<span class="status-pill ' + s.cls + '"><i aria-hidden="true">' + s.icon + '</i>' + esc(s.label) + "</span>" +
      actionBtn(d, "short") +
      '<svg class="acc-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg>' +
      "</div>" +
      '<div class="acc-panel" id="' + pid + '" role="region" aria-labelledby="' + bid + '">' +
      '<div class="acc-panel-inner"><div class="doc-detail"><div class="doc-detail-box">' +
      '<dl class="doc-facts">' +
      '<div><dt>Where to get it</dt><dd>' + esc(d.whereToGet) + "</dd></div>" +
      '<div><dt>Estimated time</dt><dd>' + esc(d.processingTime) + "</dd></div>" +
      '<div class="wide"><dt>About this document</dt><dd>' + esc(d.description) + "</dd></div>" +
      '<div><dt>Deadline</dt><dd>' + esc(d.deadline) + "</dd></div>" +
      '<div><dt>Status</dt><dd><span class="status-pill ' + s.cls + '"><i aria-hidden="true">' + s.icon + '</i>' + esc(s.label) + '</span><span class="doc-status-note">' + esc(s.note) + "</span></dd></div>" +
      "</dl>" +
      remark +
      '<div class="doc-foot">' + fileRow(d) + actionBtn(d, "long") + "</div>" +
      "</div></div></div>" +
      "</div>" +
      "</li>"
    );
  }

  function renderSummary() {
    var sch = currentScholarship();
    var docs = sch.documents;
    var total = docs.length;
    var submitted = docs.filter(function(d) {
      return d.submission && d.status !== "resubmission";
    }).length;
    var resub = docs.filter(function(d) {
      return d.status === "resubmission";
    }).length;
    var notUploaded = docs.filter(function(d) {
      return d.status === "pending" && !d.submission;
    }).length;
    var action = resub + notUploaded;

    document.getElementById("docs-scholarship").textContent = sch.name + ", " + sch.academicYear;
    document.getElementById("docs-ring-pct").textContent = submitted + "/" + total;

    var parts = [];
    if (resub) parts.push(resub + " resubmission required");
    if (notUploaded) parts.push(notUploaded + " pending");
    var alertEl = document.getElementById("docs-alert");
    var dot = document.getElementById("docs-alert-dot");
    alertEl.classList.toggle("clear", action === 0);
    dot.textContent = action === 0 ? "✓" : "!";
    document.getElementById("docs-alert-text").textContent =
      total === 0 ? "No documents are required yet" : action === 0 ? "Nothing needs your action right now" : action + " need action: " + parts.join(", ");

    var note = document.getElementById("docs-note");
    if (total === 0) {
      note.textContent = "No documents are required for this scholarship yet.";
    } else if (action === 0) {
      note.textContent = "Every document has been sent. We'll notify you if anything needs to be corrected.";
    } else {
      var due = docs.filter(needsAction).sort(function(a, b) {
        return Date.parse(a.deadline) - Date.parse(b.deadline);
      })[0];
      note.textContent = /^No deadline/.test(due.deadline) ? "No deadline has been set yet." : "Send them by " + due.deadline + ".";
    }
  }

  function render(forceOpenId) {
    var open = {};
    list.querySelectorAll(".acc-item.open").forEach(function(li) {
      open[li.getAttribute("data-doc-id")] = true;
    });
    if (forceOpenId) open[forceOpenId] = true;
    var allDocs = currentScholarship().documents;
    list.innerHTML = allDocs.length ? allDocs.map(itemHtml).join("") : '<li class="ben-empty">No documents are required for this scholarship yet.</li>';
    list.querySelectorAll(".acc-item").forEach(function(li) {
      if (open[li.getAttribute("data-doc-id")]) {
        li.classList.add("open");
        li.querySelector(".acc-trigger").setAttribute("aria-expanded", "true");
      }
    });
    renderSummary();
    SD.refreshDocs();
  }

  /* ---------- Collapse / expand ----------
     The shared accordion handler toggles on .acc-trigger. Clicks on the
     rest of the row (chevron, pill, empty space) are forwarded to it. */
  list.addEventListener("click", function(e) {
    var pv = e.target.closest("[data-preview]");
    if (pv) {
      openPreview(findDoc(pv.getAttribute("data-preview")), pv);
      return;
    }
    var act = e.target.closest(".doc-action");
    if (act) {
      openModal(act.getAttribute("data-doc-id"), act);
      return;
    }
    var head = e.target.closest(".doc-head");
    if (head && !e.target.closest(".acc-trigger")) {
      head.querySelector(".acc-trigger").click();
    }
  });

  /* ---------- Upload modal ---------- */
  var backdrop = document.getElementById("upload-backdrop");
  var modal = document.getElementById("upload-modal");
  var layout = document.querySelector(".layout");
  var input = document.getElementById("upload-input");
  var dz = document.getElementById("upload-dz");
  var body = document.getElementById("upload-body");
  var fileCard = document.getElementById("upload-file");
  var errBox = document.getElementById("upload-error");
  var errText = document.getElementById("upload-error-text");
  var progress = document.getElementById("upload-progress");
  var currentNote = document.getElementById("upload-current");
  var submitBtn = document.getElementById("upload-submit");
  var cancelBtn = document.getElementById("upload-cancel");
  var closeBtn = document.getElementById("upload-close");
  var browseBtn = document.getElementById("upload-browse");
  var changeBtn = document.getElementById("upload-change");
  var removeBtn = document.getElementById("upload-remove");
  var live = document.getElementById("docs-live");

  var activeDoc = null;
  var selectedFile = null;
  var opener = null;
  var busy = false;

  function showError(msg) {
    errText.textContent = msg;
    errBox.hidden = false;
  }

  function hideError() {
    errBox.hidden = true;
    errText.textContent = "";
  }

  function validate(file) {
    if (ALLOWED.indexOf(fileExt(file.name)) === -1)
      return "“" + file.name + "” can't be uploaded. Please choose a PDF, JPG, PNG, DOC, or DOCX file.";
    if (file.size === 0) return "“" + file.name + "” is empty. Please choose a different file.";
    if (file.size > MAX_BYTES)
      return "“" + file.name + "” is " + fmtSize(file.size) + ", which is over the 10 MB limit.";
    return "";
  }

  function setFile(file) {
    selectedFile = file;
    document.getElementById("upload-file-name").textContent = file.name;
    document.getElementById("upload-file-meta").textContent =
      fileExt(file.name).toUpperCase() + " file, " + fmtSize(file.size);
    dz.hidden = true;
    fileCard.hidden = false;
    hideError();
  }

  function clearFile() {
    selectedFile = null;
    input.value = "";
    fileCard.hidden = true;
    dz.hidden = false;
    hideError();
  }

  function handleFiles(files) {
    if (!files || !files.length) return;
    if (files.length > 1) {
      showError("Please add one file at a time.");
      return;
    }
    var problem = validate(files[0]);
    if (problem) {
      showError(problem);
      return;
    }
    setFile(files[0]);
  }

  function openModal(docId, openerEl) {
    var d = findDoc(docId);
    if (!d) return;
    activeDoc = d;
    opener = openerEl || null;
    busy = false;
    clearFile();

    document.getElementById("upload-doc-name").textContent = d.name;
    document.getElementById("upload-doc-meta").textContent = "Deadline: " + d.deadline;

    if (d.submission) {
      var f = d.submission;
      var nameHtml = f.url ?
        '<button type="button" class="doc-link" data-preview="' + esc(d.id) + '">' + esc(f.fileName) + "</button>" :
        "<strong>" + esc(f.fileName) + "</strong>";
      var extra =
        d.status === "verified" ?
        " This document is already verified. A new file will be sent back for verification." :
        " Uploading a new file will replace it.";
      currentNote.innerHTML = "Current file: " + nameHtml + ", submitted " + esc(f.submittedOn) + "." + extra;
      currentNote.hidden = false;
    } else {
      currentNote.hidden = true;
    }

    submitBtn.disabled = false;
    submitBtn.textContent = "Upload";
    cancelBtn.disabled = false;
    closeBtn.disabled = false;
    progress.hidden = true;
    progress.classList.remove("run");

    backdrop.hidden = false;
    document.body.classList.add("modal-open");
    layout.setAttribute("aria-hidden", "true");
    if ("inert" in layout) layout.inert = true;
    browseBtn.focus();
  }

  function closeModal(skipFocus) {
    if (busy) return;
    backdrop.hidden = true;
    document.body.classList.remove("modal-open");
    layout.removeAttribute("aria-hidden");
    if ("inert" in layout) layout.inert = false;
    activeDoc = null;
    selectedFile = null;
    if (!skipFocus && opener && document.body.contains(opener)) opener.focus();
    opener = null;
  }

  function submit() {
    if (busy || !activeDoc) return;
    if (!selectedFile) {
      showError("Please choose a file to upload first.");
      (dz.hidden ? changeBtn : browseBtn).focus();
      return;
    }
    hideError();
    busy = true;
    submitBtn.disabled = true;
    submitBtn.textContent = "Uploading…";
    cancelBtn.disabled = true;
    closeBtn.disabled = true;
    changeBtn.disabled = true;
    removeBtn.disabled = true;
    progress.hidden = false;
    void progress.offsetWidth;
    progress.classList.add("run");

    var doc = activeDoc;
    var file = selectedFile;
    setTimeout(function() {
      if (doc.submission && doc.submission.url) URL.revokeObjectURL(doc.submission.url);
      var mime = mimeFor(file);
      var stored = new Blob([file], {
        type: mime
      });
      doc.submission = {
        fileName: file.name,
        fileType: fileExt(file.name).toUpperCase(),
        submittedOn: fmtDate(new Date()),
        url: URL.createObjectURL(stored),
        mime: mime,
        size: file.size
      };
      saveUpload(doc, stored, doc.submission);
      doc.status = "pending";
      doc.reviewerNote = null;

      busy = false;
      changeBtn.disabled = false;
      removeBtn.disabled = false;
      closeModal(true);
      render(doc.id);
      live.textContent = doc.name + " was uploaded. Status is Pending until the officer verifies it.";
      var again = list.querySelector('.acc-item[data-doc-id="' + doc.id + '"] .doc-head .doc-action');
      if (again) again.focus();
    }, 900);
  }

  browseBtn.addEventListener("click", function() {
    input.click();
  });
  changeBtn.addEventListener("click", function() {
    input.click();
  });
  removeBtn.addEventListener("click", function() {
    clearFile();
    browseBtn.focus();
  });
  input.addEventListener("change", function() {
    handleFiles(input.files);
    input.value = "";
  });
  submitBtn.addEventListener("click", submit);
  cancelBtn.addEventListener("click", function() {
    closeModal();
  });
  closeBtn.addEventListener("click", function() {
    closeModal();
  });
  backdrop.addEventListener("mousedown", function(e) {
    if (e.target === backdrop) closeModal();
  });

  /* Drag and drop (anywhere in the modal body) */
  var dragDepth = 0;
  body.addEventListener("dragenter", function(e) {
    e.preventDefault();
    dragDepth++;
    dz.classList.add("drag");
  });
  body.addEventListener("dragover", function(e) {
    e.preventDefault();
    if (e.dataTransfer) e.dataTransfer.dropEffect = "copy";
  });
  body.addEventListener("dragleave", function() {
    dragDepth = Math.max(0, dragDepth - 1);
    if (!dragDepth) dz.classList.remove("drag");
  });
  body.addEventListener("drop", function(e) {
    e.preventDefault();
    dragDepth = 0;
    dz.classList.remove("drag");
    if (!busy) handleFiles(e.dataTransfer && e.dataTransfer.files);
  });
  /* A file dropped outside the drop area must not navigate away */
  ["dragover", "drop"].forEach(function(evt) {
    window.addEventListener(evt, function(e) {
      if (!backdrop.hidden) e.preventDefault();
    });
  });

  /* Escape to close, Tab stays inside the dialog */
  document.addEventListener("keydown", function(e) {
    if (backdrop.hidden) return;
    if (e.key === "Escape") {
      e.preventDefault();
      closeModal();
      return;
    }
    if (e.key !== "Tab") return;
    var focusables = Array.prototype.filter.call(
      modal.querySelectorAll('a[href], button, input, [tabindex]:not([tabindex="-1"])'),
      function(el) {
        return !el.disabled && el.type !== "file" && el.offsetParent !== null;
      }
    );
    if (!focusables.length) return;
    var first = focusables[0];
    var last = focusables[focusables.length - 1];
    if (e.shiftKey && (document.activeElement === first || !modal.contains(document.activeElement))) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && (document.activeElement === last || !modal.contains(document.activeElement))) {
      e.preventDefault();
      first.focus();
    }
  });

  /* ---------- Real file storage (IndexedDB) ----------
     Each uploaded file is kept in this browser, per signed-in scholar and
     per requirement, so it is still there after a refresh. */
  var IDB_NAME = "scholarUploads";
  var IDB_STORE = "files";

  function mimeFor(file) {
    var ext = fileExt(file.name);
    if (ext === "pdf") return "application/pdf";
    if (ext === "jpg" || ext === "jpeg") return "image/jpeg";
    if (ext === "png") return "image/png";
    if (ext === "doc") return "application/msword";
    if (ext === "docx") return "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
    return file.type || "application/octet-stream";
  }

  function openIdb() {
    return new Promise(function(resolve, reject) {
      if (!window.indexedDB) {
        reject(new Error("IndexedDB unavailable"));
        return;
      }
      var req = indexedDB.open(IDB_NAME, 1);
      req.onupgradeneeded = function() {
        req.result.createObjectStore(IDB_STORE, {
          keyPath: "key"
        });
      };
      req.onsuccess = function() {
        resolve(req.result);
      };
      req.onerror = function() {
        reject(req.error);
      };
    });
  }

  function uploadKey(d) {
    return [SD.userKey, SD.term.term_id, d.id].join("|");
  }

  function saveUpload(d, blob, sub) {
    openIdb().then(function(db) {
      var tx = db.transaction(IDB_STORE, "readwrite");
      tx.objectStore(IDB_STORE).put({
        key: uploadKey(d),
        blob: blob,
        fileName: sub.fileName,
        fileType: sub.fileType,
        submittedOn: sub.submittedOn,
        mime: sub.mime,
        size: sub.size
      });
    }).catch(function() {
      /* Storage blocked: the file still works for this visit. */
    });
  }

  function loadUploads() {
    openIdb().then(function(db) {
      var docsList = currentScholarship().documents;
      var left = docsList.length;
      if (!left) return;
      docsList.forEach(function(d) {
        var req = db.transaction(IDB_STORE, "readonly").objectStore(IDB_STORE).get(uploadKey(d));
        req.onsuccess = function() {
          var rec = req.result;
          if (rec && rec.blob) {
            d.submission = {
              fileName: rec.fileName,
              fileType: rec.fileType,
              submittedOn: rec.submittedOn,
              url: URL.createObjectURL(rec.blob),
              mime: rec.mime,
              size: rec.size
            };
            d.status = "pending";
            d.reviewerNote = null;
          }
          if (--left === 0) render();
        };
        req.onerror = function() {
          if (--left === 0) render();
        };
      });
    }).catch(function() {});
  }

  /* ---------- Preview ---------- */
  var pvBackdrop = document.getElementById("preview-backdrop");
  var pvModal = document.getElementById("preview-modal");
  var pvStage = document.getElementById("preview-stage");
  var pvName = document.getElementById("preview-name");
  var pvMeta = document.getElementById("preview-meta");
  var pvClose = document.getElementById("preview-close");
  var pvDone = document.getElementById("preview-done");
  var pvDownload = document.getElementById("preview-download");
  var pvDoc = null;
  var pvOpener = null;

  function openPreview(d, openerEl) {
    if (!d || !d.submission || !d.submission.url) return;
    var f = d.submission;
    var ext = f.fileType.toLowerCase();
    pvDoc = d;
    pvOpener = openerEl || null;
    pvName.textContent = f.fileName;
    pvMeta.textContent = d.name + " \u00B7 " + f.fileType + (f.size ? ", " + fmtSize(f.size) : "") + ", submitted " + f.submittedOn;
    if (ext === "jpg" || ext === "jpeg" || ext === "png") {
      pvStage.innerHTML = '<img src="' + esc(f.url) + '" alt="' + esc(f.fileName) + '" />';
    } else if (ext === "pdf") {
      pvStage.innerHTML = '<iframe src="' + esc(f.url) + '" title="' + esc(f.fileName) + '"></iframe>';
    } else {
      pvStage.innerHTML = '<div class="preview-fallback"><strong>Preview isn\u2019t available for ' + esc(f.fileType) +
        ' files in the browser.</strong>Download the file to open it in Word.</div>';
    }
    pvBackdrop.hidden = false;
    pvClose.focus();
  }

  function closePreview() {
    if (pvBackdrop.hidden) return;
    pvBackdrop.hidden = true;
    pvStage.innerHTML = "";
    pvDoc = null;
    if (pvOpener && document.body.contains(pvOpener)) pvOpener.focus();
    pvOpener = null;
  }

  pvClose.addEventListener("click", closePreview);
  pvDone.addEventListener("click", closePreview);
  pvBackdrop.addEventListener("mousedown", function(e) {
    if (e.target === pvBackdrop) closePreview();
  });
  pvDownload.addEventListener("click", function() {
    if (!pvDoc || !pvDoc.submission) return;
    var a = document.createElement("a");
    a.href = pvDoc.submission.url;
    a.download = pvDoc.submission.fileName;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  });
  /* Preview sits on top of the upload dialog: it handles Escape and Tab first. */
  document.addEventListener("keydown", function(e) {
    if (pvBackdrop.hidden) return;
    if (e.key === "Escape") {
      e.preventDefault();
      e.stopPropagation();
      closePreview();
      return;
    }
    if (e.key === "Tab") {
      e.stopPropagation();
      var f = Array.prototype.filter.call(pvModal.querySelectorAll("button"), function(el) {
        return !el.disabled && el.offsetParent !== null;
      });
      if (!f.length) return;
      var first = f[0],
        last = f[f.length - 1];
      if (e.shiftKey && (document.activeElement === first || !pvModal.contains(document.activeElement))) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && (document.activeElement === last || !pvModal.contains(document.activeElement))) {
        e.preventDefault();
        first.focus();
      }
    }
  }, true);

  /* Preview button inside the upload dialog's "Current file" note */
  currentNote.addEventListener("click", function(e) {
    var pv = e.target.closest("[data-preview]");
    if (pv && activeDoc) openPreview(activeDoc, pv);
  });

  render();
  loadUploads();
})();

/* ---------- next block ---------- */
/* =====================================================================
     BENEFITS: data + rendering.
     Each benefit is one database row with exactly four fields:
       benefit_name, benefit_type, description, amount
     benefit_type decides the section:
       "stipend" -> Stipend section (shows name, description, amount)
       "other"   -> Other Benefits section (plain text: name, description)
     ===================================================================== */
(function() {
  "use strict";

  // scholarship_benefits rows of the scholar's scholarship (shared database above).
  var SD = window.SD;
  var BENEFITS = SD.benefits;

  function esc(v) {
    return String(v).replace(/[&<>"']/g, function(c) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;"
      } [c];
    });
  }

  function peso(n) {
    return "\u20B1" + Number(n).toLocaleString("en-PH", {
      maximumFractionDigits: 2
    });
  }

  /* "₱25,000 / semester" -> big amount, small unit (same text as everywhere else). */
  function amountHtml(text) {
    var i = text.indexOf(" / ");
    return i < 0 ? esc(text) : esc(text.slice(0, i)) + "<small>" + esc(text.slice(i + 1)) + "</small>";
  }

  function byType(t) {
    return BENEFITS.filter(function(b) {
      return String(b.benefit_type).toLowerCase() === t;
    });
  }

  /* Stipend section */
  var stipends = byType("stipend");
  document.getElementById("stipend-grid").innerHTML = stipends.length ?
    stipends.map(function(b) {
      return (
        '<li class="col ben-tile featured">' +
        '<div class="ben-main"><div class="doc-name">' + esc(b.benefit_name) + "</div>" +
        '<p class="ben-desc">' + esc(SD.orNone(b.description, "No description provided.")) + "</p>" +
        '<div class="ben-amount">' + amountHtml(SD.stipendText(b)) + "</div></div>" +
        "</li>"
      );
    }).join("") :
    '<li class="ben-empty">No stipend is listed for this scholarship.</li>';

  /* Other benefits section: same tile design as the stipend (name + description) */
  var others = byType("other");
  document.getElementById("other-grid").innerHTML = others.length ?
    others.map(function(b) {
      return (
        '<li class="col ben-tile featured">' +
        '<div class="ben-main"><div class="doc-name">' + esc(b.benefit_name) + "</div>" +
        '<p class="ben-desc">' + esc(SD.orNone(b.description, "No description provided.")) + "</p>" +
        "</div></li>"
      );
    }).join("") :
    '<li class="ben-empty">This scholarship has no other benefits.</li>';
})();
