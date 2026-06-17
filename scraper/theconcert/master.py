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
    from urllib.parse import urljoin
    _dbg("IMPORT OK: urllib.parse.urljoin")
except Exception:
    _dbg(f"IMPORT FAILED: urllib.parse\n{traceback.format_exc()}", "ERROR")
    raise

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
    
    edge_options.binary_location = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
    _dbg(f"Edge binary set to: {edge_options.binary_location} | exists={os.path.exists(edge_options.binary_location)}")
    
    edge_options.add_argument("--log-level=3")
    edge_options.add_experimental_option("excludeSwitches", ["enable-logging"])

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

        last_height = driver.execute_script("return document.body.scrollHeight")
        _dbg(f"Initial scroll height: {last_height}")
        scroll_count = 0
        while True:
            driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
            time.sleep(2)
            new_height = driver.execute_script("return document.body.scrollHeight")
            scroll_count += 1
            _dbg(f"Scroll {scroll_count}: height {last_height} -> {new_height}")
            if new_height == last_height:
                _dbg("Scroll height stable — done scrolling")
                break
            last_height = new_height

        target_selector = "div.concert-list a"
        _dbg(f"Waiting for selector: '{target_selector}'")
        try:
            WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, target_selector)))
            _dbg("Selector found")
            a_elements = driver.find_elements(By.CSS_SELECTOR, target_selector)
            _dbg(f"Found {len(a_elements)} anchor elements")
            for a in a_elements:
                try:
                    href = a.get_attribute("href")
                    if href and "/concert/" in href and "login" not in href:
                        full_url = urljoin(listing_url, href)
                        if full_url not in links:
                            links.append(full_url)
                            _dbg(f"Collected URL: {full_url}")
                except Exception:
                    _dbg(f"Exception reading anchor href\n{traceback.format_exc()}", "WARN")
                    continue
        except Exception:
            _dbg(f"Timed out or failed waiting for '{target_selector}'\n{traceback.format_exc()}", "WARN")
            _dbg(f"Page source snippet:\n{driver.page_source[:1000]}", "WARN")
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
    MAIN_PAGE_URL = "https://www.theconcert.com/concert"
    ORIGIN_NAME = "The Concert"
    try:
        update_laravel(job_id, status="running", progress=5)
        _dbg("Collecting concert URLs...")
        concert_urls = get_all_concert_links(MAIN_PAGE_URL)
        total = len(concert_urls)
        _dbg(f"Total concert URLs collected: {total}")

        if total == 0:
            _dbg("No URLs found — marking job as failed", "WARN")
            update_laravel(job_id, status="failed", error_message="No URLs found.")
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
        update_laravel(job_id, status="completed", progress=100)
        _dbg("start_scraping COMPLETE")
    except Exception:
        _dbg(f"Fatal exception in start_scraping\n{traceback.format_exc()}", "ERROR")
        update_laravel(job_id, status="failed", error_message=str(traceback.format_exc()))