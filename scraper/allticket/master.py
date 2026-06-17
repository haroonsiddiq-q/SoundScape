from selenium import webdriver
from selenium.webdriver.edge.service import Service
from selenium.webdriver.edge.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import requests
import sys
import os
import math

# ── DEBUG HELPER ──────────────────────────────────────────────────────────────
import traceback
import datetime

_current_file = os.path.abspath(__file__)
_base_dir = os.path.dirname(os.path.dirname(os.path.dirname(_current_file)))
_LOG_PATH = os.path.join(_base_dir, 'storage', 'logs', 'scraper_debug.log')

def _dbg(msg, level="INFO"):
    try:
        ts = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        line = f"[{ts}] [{level}] {msg}\n"
        with open(_LOG_PATH, "a", encoding="utf-8") as f:
            f.write(line)
            f.flush()  # Add this to ensure immediate write
    except Exception as e:
        print(f"[DEBUG LOG FAILED] {e}")  # Fallback to stdout
        pass

_dbg("=" * 60)
_dbg("MODULE LOAD STARTED")
_dbg(f"__file__     = {os.path.abspath(__file__)}")
_dbg(f"Python       = {sys.version}")
_dbg(f"Working dir  = {os.getcwd()}")
# ─────────────────────────────────────────────────────────────────────────────

current_dir = os.path.dirname(os.path.abspath(__file__))
parent_dir = os.path.dirname(current_dir)
sys.path.append(parent_dir)

_dbg(f"current_dir  = {current_dir}")
_dbg(f"parent_dir   = {parent_dir}")
_dbg(f"sys.path     = {sys.path}")

# ── DEBUG: wrap risky imports so we can see exactly which one fails ───────────
try:
    from utils.artist_matcher import extract_artists_from_text
    _dbg("IMPORT OK: utils.artist_matcher")
except Exception:
    _dbg(f"IMPORT FAILED: utils.artist_matcher\n{traceback.format_exc()}", "ERROR")
    raise

try:
    from .tester import get_page_destination_data, save_concert
    _dbg("IMPORT OK: .tester")
except Exception:
    _dbg(f"IMPORT FAILED: .tester\n{traceback.format_exc()}", "ERROR")
    raise
# ─────────────────────────────────────────────────────────────────────────────

_dbg("MODULE LOAD COMPLETE")


def update_laravel(job_id, status=None, progress=None, new_result=None, error_message=None):
    _dbg(f"update_laravel called | job_id={job_id} status={status} progress={progress} new_result={new_result} error={error_message}")
    try:
        current_file_path = os.path.abspath(__file__)
        base_dir = os.path.dirname(os.path.dirname(os.path.dirname(current_file_path)))
        kill_file = os.path.join(base_dir, 'storage', 'logs', f'cancel_{job_id}.txt')
        
        _dbg(f"kill_file path = {kill_file} | exists={os.path.exists(kill_file)}")
        if os.path.exists(kill_file):
            _dbg("Kill file found — exiting process", "WARN")
            try:
                os.remove(kill_file) 
            except Exception:
                pass
            os._exit(1)
    except Exception:
        _dbg(f"Exception in kill-file check\n{traceback.format_exc()}", "ERROR")
    
    url = f"http://127.0.0.1:8000/api/scraper/update/{job_id}"
    data = {}
    if status: data["status"] = status
    if progress is not None: data["progress"] = progress
    if new_result: data["new_result"] = new_result
    if error_message: data["error_message"] = error_message
    
    _dbg(f"POST {url} | payload={data}")
    try:
        response = requests.post(url, json=data, timeout=5)
        response.raise_for_status()
        _dbg(f"Laravel response: {response.status_code}")
    except Exception:
        _dbg(f"Laravel POST failed\n{traceback.format_exc()}", "ERROR")


def get_all_concert_links(listing_url):
    _dbg(f"get_all_concert_links START | url={listing_url}")
    links = []
    edge_options = Options()
    edge_options.add_argument("--headless=new")
    edge_options.add_argument("--window-size=1920,1080")
    user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0"
    edge_options.add_argument(f"user-agent={user_agent}")
    edge_options.add_argument("--disable-gpu")
    edge_options.add_argument("--disable-blink-features=AutomationControlled")
    edge_options.add_experimental_option("excludeSwitches", ["enable-automation"])

    driver_path = os.path.join(parent_dir, "msedgedriver.exe")
    _dbg(f"Edge driver path = {driver_path} | exists={os.path.exists(driver_path)}")
    service = Service(executable_path=driver_path)

    _dbg("Launching Edge WebDriver...")
    try:
        driver = webdriver.Edge(service=service, options=edge_options)
        _dbg("Edge WebDriver launched OK")
    except Exception:
        _dbg(f"Edge WebDriver FAILED to launch\n{traceback.format_exc()}", "ERROR")
        return links

    try:
        _dbg(f"Navigating to {listing_url}")
        driver.get(listing_url)
        _dbg(f"Page loaded. Title='{driver.title}'")
        time.sleep(3)

        try:
            WebDriverWait(driver, 15).until(EC.presence_of_element_located((By.CSS_SELECTOR, ".btn-buy-now")))
            _dbg("'.btn-buy-now' element found")
        except Exception:
            _dbg("Timed out waiting for '.btn-buy-now' — returning empty list", "WARN")
            _dbg(f"Page source snippet:\n{driver.page_source[:1000]}", "WARN")
            return []

        buttons = driver.find_elements(By.CSS_SELECTOR, ".ticket .btn")
        total_events = len(buttons)
        _dbg(f"Found {total_events} ticket buttons")

        for i in range(total_events):
            _dbg(f"Processing button {i+1}/{total_events}")
            try:
                WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, ".btn-buy-now")))
                current_buttons = driver.find_elements(By.CSS_SELECTOR, ".btn-buy-now")
                if i >= len(current_buttons):
                    _dbg(f"Button index {i} out of range (only {len(current_buttons)} found), skipping", "WARN")
                    continue
                target_button = current_buttons[i]
                driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", target_button)
                time.sleep(1)
                driver.execute_script("arguments[0].click();", target_button)
                WebDriverWait(driver, 10).until(lambda d: "/event/" in d.current_url)
                
                if driver.current_url not in links:
                    links.append(driver.current_url)
                    _dbg(f"Collected URL: {driver.current_url}")
                driver.back()
                time.sleep(3)
            except Exception:
                _dbg(f"Exception on button {i}\n{traceback.format_exc()}", "WARN")
                try:
                    if "category/concert" not in driver.current_url:
                        _dbg(f"Unexpected URL '{driver.current_url}', going back", "WARN")
                        driver.back()
                        time.sleep(3)
                except Exception:
                    _dbg(f"Exception during recovery back()\n{traceback.format_exc()}", "ERROR")
    except Exception:
        _dbg(f"Exception in get_all_concert_links main block\n{traceback.format_exc()}", "ERROR")
    finally:
        _dbg("Quitting WebDriver")
        driver.quit()

    _dbg(f"get_all_concert_links END | collected {len(links)} links")
    return links


def trigger_cleanup(origin_name):
    _dbg(f"trigger_cleanup | origin={origin_name}")
    url = "http://127.0.0.1:8000/api/concerts/cleanup"
    try:
        requests.post(url, json={"origin": origin_name}, timeout=10)
        _dbg("Cleanup POST sent OK")
    except Exception:
        _dbg(f"Cleanup POST failed\n{traceback.format_exc()}", "ERROR")


def start_scraping(job_id):
    _dbg(f"start_scraping CALLED | job_id={job_id}")
    MAIN_PAGE_URL = "https://www.allticket.com/category/concert"
    ORIGIN_NAME = "All Ticket"
    try:
        update_laravel(job_id, status='running', progress=5)
        _dbg("Collecting concert URLs...")
        concert_urls = get_all_concert_links(MAIN_PAGE_URL)
        total = len(concert_urls)
        _dbg(f"Total concert URLs collected: {total}")

        if total == 0:
            _dbg("No URLs found — marking job as failed", "WARN")
            update_laravel(job_id, status='failed', error_message="No URLs found.")
            return

        for i, url in enumerate(concert_urls):
            current_progress = 5 + math.floor(((i + 1) / total) * 90)
            _dbg(f"Processing concert {i+1}/{total} | progress={current_progress}% | url={url}")
            try:
                concert_data = get_page_destination_data(url, headless=True, timeout=20)
                if concert_data:
                    title = concert_data.get("name", "Unknown")
                    _dbg(f"  Concert data retrieved: '{title}'")
                    full_text = f"{title} {concert_data.get('description', '')}"
                    concert_data["artists"] = extract_artists_from_text(full_text)
                    _dbg(f"  Artists extracted: {concert_data['artists']}")
                    save_concert(concert_data)
                    _dbg(f"  Concert saved OK")
                    update_laravel(job_id, progress=current_progress, new_result=title)
                else:
                    _dbg(f"  get_page_destination_data returned None/empty for {url}", "WARN")
            except Exception:
                _dbg(f"  Exception processing {url}\n{traceback.format_exc()}", "ERROR")

        _dbg("All concerts processed. Triggering cleanup...")
        trigger_cleanup(ORIGIN_NAME)
        update_laravel(job_id, status='completed', progress=100)
        _dbg("start_scraping COMPLETE")
    except Exception:
        _dbg(f"Fatal exception in start_scraping\n{traceback.format_exc()}", "ERROR")
        update_laravel(job_id, status='failed', error_message=str(traceback.format_exc()))