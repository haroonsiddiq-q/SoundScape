# 🎶 SoundScape

**SoundScape** เป็นเว็บแอปพลิเคชันแพลตฟอร์มสำหรับรวบรวม ค้นหา และติดตามConcertต่างๆ แบบครบวงจร ช่วยให้ผู้ใช้งานไม่พลาดทุกงานดนตรีที่น่าสนใจ พร้อมแสดงตำแหน่งการจัดงานบนแผนที่แบบอินเทอร์แอกทีฟ นอกจากนี้ยังมีระบบจัดการสำหรับผู้จัดงาน (Promoter) และระบบดึงข้อมูลอัตโนมัติจากผู้ให้บริการทิคเก็ตชั้นนำ

> ⚠️ **หมายเหตุ:** โปรเจกต์นี้เป็นเพียงโปรเจกต์ของนักศึกษาที่จัดทำขึ้นเพื่อการศึกษาและการเรียนรู้เท่านั้น ไม่ได้มีจุดประสงค์เพื่อการแสวงหากำไรหรือใช้งานในเชิงพาณิชย์จริงแต่อย่างใด

---

## ✨ ฟีเจอร์หลัก (Key Features)

### 🗺️ 1. แผนที่Concert (Interactive Map)
- แสดงตำแหน่งของConcertและสถานที่จัดงานบนแผนที่แบบอินเทอร์แอกทีฟ (ใช้งานผ่านพิกัดละติจูด/ลองจิจูดด้วย Leaflet.js)
- ผู้ใช้สามารถค้นหาConcertตามพื้นที่และดูรายละเอียดของงานได้โดยตรงจากหมุดบนแผนที่

### 🎫 2. ระบบผู้จัดงาน (Promoter Management)
- **แดชบอร์ดสำหรับโปรโมเตอร์**: มีพื้นที่เฉพาะสำหรับโปรโมเตอร์ (Promoter Dashboard) เพื่อให้ผู้จัดงานเข้ามาจัดการConcertของตนเอง
- **การจัดการConcert**: สามารถ เพิ่ม/Edit/Delete ข้อมูลConcert อัปเดตStatus และจัดการรายละเอียดศิลปินที่เข้าร่วมงานได้

### 🔔 3. ระบบการแจ้งเตือน (Notifications & LINE Integration)
- **การแจ้งเตือนภายในระบบ**: แจ้งเตือนผู้ใช้เมื่อมีการเปลี่ยนแปลงข้อมูลConcertที่ติดตาม หรือมีประกาศสำคัญ
- **LINE Integration**: รองรับการเชื่อมต่อและแจ้งเตือนผ่านแอปพลิเคชัน LINE (LINE Flex Messages) เพื่อให้ผู้ใช้รับทราบข่าวสารได้อย่างรวดเร็ว

### 🤖 4. ระบบดึงข้อมูลอัตโนมัติ (Web Scraper)
- มีชุดสคริปต์ Python อัจฉริยะ (Scraper) ที่ทำงานแยกส่วน เพื่อดึงข้อมูลConcertจากเว็บไซต์ผู้จำหน่ายตั๋วชั้นนำแบบอัตโนมัติ เช่น:
  - **AllTicket**
  - **TheConcert**
  - **Ticketier**
- มีระบบ Admin Panel ไว้สำหรับควบคุมและตรวจสอบStatusการทำงานของ Scraper (Scraper Jobs) 

### 👥 5. ระบบผู้ใช้งานและคอมเมนต์ (Users & Interactions)
- ระบบสมัครสมาชิก จัดการโปรไฟล์ (ทำงานร่วมกับ Laravel Jetstream / Fortify)
- ผู้ใช้สามารถกดติดตามConcert และร่วมแสดงความคิดเห็น (Comments) ในหน้าConcertต่างๆ ได้

### 👑 6. ระบบแอดมิน (Admin Panel)
- จัดการข้อมูลทั้งหมดในระบบ รวมถึง ผู้ใช้งาน, โปรโมเตอร์ที่รอการอนุมัติ, Concert, ข้อมูลศิลปิน, และไฮไลท์ (Highlight Banner) บนหน้าแรก

---

## 🛠️ โครงสร้างเทคโนโลยี (Tech Stack)

**Backend:**
- [Laravel 11](https://laravel.com/) (PHP)
- MySQL / MariaDB (Database)

**Frontend:**
- [Vue.js 3](https://vuejs.org/)
- [Inertia.js](https://inertiajs.com/) (Server-Driven Single Page App)
- [Tailwind CSS](https://tailwindcss.com/) (Styling)

**Scraper:**
- [Python 3](https://www.python.org/) (Selenium, BeautifulSoup)

---

## 🚀 การติดตั้งและรันโปรเจกต์ (Installation & Setup)

### ข้อกำหนดเบื้องต้น (Prerequisites)
- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL หรือฐานข้อมูลที่รองรับ
- Python 3.x (สำหรับการรัน Scraper)

### ขั้นตอนการติดตั้ง

1. **โคลนโปรเจกต์และติดตั้ง Dependencies ฝั่ง PHP/Node.js**
   ```bash
   git clone <repository-url>
   cd soundscape
   composer install
   npm install
   ```
2. **คัดลอกไฟล์ Environment และสร้าง App Key**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
3. **ตั้งค่าฐานข้อมูล (Database):** เปิดไฟล์ .env และกำหนดค่าการเชื่อมต่อฐานข้อมูลของคุณ
    ```bash
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=soundscape
    DB_USERNAME=root
    DB_PASSWORD=
    ```
4. **รัน Migration และ Seeder:** (เพื่อสร้างตารางและข้อมูลจำลอง)
    ```bash
    php artisan migrate --seed
    ```
5. **รันเซิร์ฟเวอร์:** เปิด 2 Terminal เพื่อรันฝั่ง Backend และ Frontend:
    ```bash
    # Terminal 1: เริ่มต้น Laravel Development Server
    php artisan serve

    # Terminal 2: เริ่มต้น Vite สำหรับการพัฒนา Frontend
    npm run dev
    ```

---

## 🐍 การติดตั้งระบบ Scraper (Python)

หากต้องการใช้งานระบบดึงข้อมูลอัตโนมัติ ให้เข้าไปที่โฟลเดอร์ scraper และติดตั้งไDeleteรารีที่จำเป็น
```bash
cd scraper
pip install -r requirements.txt
cp .env.example .env
```
(คุณอาจต้องติดตั้ง WebDriver เช่น msedgedriver.exe ให้ตรงกับเบราว์เซอร์ของคุณเพื่อใช้งาน Selenium)