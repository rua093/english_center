USE english_center_db;

-- Sample data for extracurricular_activities
-- Fields: title, description, content, location, image_thumbnail, fee, start_date, status

INSERT INTO extracurricular_activities (
    title,
    description,
    content,
    location,
    image_thumbnail,
    fee,
    start_date,
    status
)
SELECT
    'English Camp 2026',
    'Hoat dong ket noi hoc vien qua trai nghiem thuc te.',
    '<p>Chu de: giao tiep, teamwork, va tu tin noi tieng Anh trong cac tinh huong gan voi doi song.</p>',
    'Co so 1 - San trung tam',
    '/assets/images/center.jpg',
    200000,
    '2026-05-10',
    'upcoming'
WHERE NOT EXISTS (
    SELECT 1 FROM extracurricular_activities WHERE title = 'English Camp 2026'
);

INSERT INTO extracurricular_activities (
    title,
    description,
    content,
    location,
    image_thumbnail,
    fee,
    start_date,
    status
)
SELECT
    'Speaking Day Challenge',
    'San choi speaking giup hoc vien phan xa va trinh bay y tuong.',
    '<p>Hoc vien lam viec theo nhom, thuyet trinh ngan va nhan phan hoi truc tiep tu giao vien.</p>',
    'Co so 2 - Phong 302',
    '/assets/images/student.jpg',
    0,
    '2026-04-28',
    'ongoing'
WHERE NOT EXISTS (
    SELECT 1 FROM extracurricular_activities WHERE title = 'Speaking Day Challenge'
);

INSERT INTO extracurricular_activities (
    title,
    description,
    content,
    location,
    image_thumbnail,
    fee,
    start_date,
    status
)
SELECT
    'Parents & Students Open Day',
    'Ngay hoi mo cua danh cho phu huynh va hoc vien moi.',
    '<p>Gioi thieu chuong trinh hoc, hoat dong lop, va tu van lo trinh phu hop tung hoc vien.</p>',
    'Co so 1 - Hoi truong lon',
    '/assets/images/student2.jpg',
    150000,
    '2026-05-18',
    'upcoming'
WHERE NOT EXISTS (
    SELECT 1 FROM extracurricular_activities WHERE title = 'Parents & Students Open Day'
);

INSERT INTO extracurricular_activities (
    title,
    description,
    content,
    location,
    image_thumbnail,
    fee,
    start_date,
    status
)
SELECT
    'Summer Fun English Day',
    'Ngay hoi he voi tro choi, mini game va workshop phat am.',
    '<p>Hoat dong tap trung vao tu vung, phat am, va giao tiep tu nhien trong moi truong vui ve.</p>',
    'Co so 3 - San vuon',
    '/assets/images/student3.jpg',
    120000,
    '2026-06-05',
    'upcoming'
WHERE NOT EXISTS (
    SELECT 1 FROM extracurricular_activities WHERE title = 'Summer Fun English Day'
);

INSERT INTO extracurricular_activities (
    title,
    description,
    content,
    location,
    image_thumbnail,
    fee,
    start_date,
    status
)
SELECT
    'Debate & Presentation Club',
    'Cau lac bo tranh luan va thuyet trinh cho hoc vien nang cao.',
    '<p>Cac nhom hoc vien luyen lap luan, trinh bay quan diem va phan bien trong moi chu de gan gui.</p>',
    'Co so 2 - Phong hoi thao',
    '/assets/images/student_girl.png',
    300000,
    '2026-04-20',
    'finished'
WHERE NOT EXISTS (
    SELECT 1 FROM extracurricular_activities WHERE title = 'Debate & Presentation Club'
);

INSERT INTO extracurricular_activities (
    title,
    description,
    content,
    location,
    image_thumbnail,
    fee,
    start_date,
    status
)
SELECT
    'Field Trip English Tour',
    'Chuyen di trai nghiem ket hop noi tieng Anh ngoai trung tam.',
    '<p>Hoc vien tham gia nhiem vu giao tiep, thu thap thong tin, va bao cao ket qua sau chuyen di.</p>',
    'Ngoai truong - Dia diem doi tac',
    '/assets/images/tu_van_student.jpg',
    250000,
    '2026-05-25',
    'upcoming'
WHERE NOT EXISTS (
    SELECT 1 FROM extracurricular_activities WHERE title = 'Field Trip English Tour'
);
