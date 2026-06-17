import sys
import os
import re
import requests
import datetime
import time

current_dir = os.path.dirname(os.path.abspath(__file__))
parent_dir = os.path.dirname(current_dir)
sys.path.append(parent_dir)

from selenium import webdriver
from selenium.webdriver.edge.service import Service
from selenium.webdriver.edge.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from utils.constants import months_map, province_map
from utils.geocoder import get_coordinates

def get_page_destination_data(url, headless=True, timeout=10):
    edge_options = Options()
    edge_options.add_argument("--log-level=3")
    edge_options.add_experimental_option('excludeSwitches', ['enable-logging'])

    if headless:
        edge_options.add_argument("--headless=new") 
        edge_options.add_argument("--window-size=1920,1080")
        user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36"
        edge_options.add_argument(f'user-agent={user_agent}')
        edge_options.add_argument("--disable-blink-features=AutomationControlled")

    driver = None
    driver_path = os.path.join(parent_dir, "msedgedriver.exe")
    service = Service(executable_path=driver_path)

    try:
        if not os.path.exists(driver_path):
            raise FileNotFoundError(f"Edge Driver file not found at: {driver_path}")

        driver = webdriver.Edge(service=service, options=edge_options)
        driver.set_page_load_timeout(timeout)

        driver.get(url)

        driver.execute_script("window.scrollTo(0, 300);")
        time.sleep(2) 

        # NAME
        name = None
        try:
            name_selector = ".eventDescHeader h4"
            name_element = WebDriverWait(driver, 10).until(
                EC.visibility_of_element_located((By.CSS_SELECTOR, name_selector))
            )
            name = name_element.text.strip()
        except:
            name = None

        # DESCRIPTION
        description = "No Description"
        try:
            info_wrapper = WebDriverWait(driver, 10).until(
                EC.visibility_of_element_located(
                    (By.XPATH, "//div[contains(@class, 'wrapper-event-info')]")
                )
            )
            raw_desc = info_wrapper.text.strip()
            description = re.sub(r'\n(?:\s*\n)+', '\n\n', raw_desc)
        except:
            description = "No Description"

        # DATE START & END
        date_start, date_end = None, None
        try:
            header_element = WebDriverWait(driver, 10).until(
                EC.visibility_of_element_located((By.CSS_SELECTOR, ".eventDescHeader"))
            )
            full_text = header_element.text.upper() 
            full_text = full_text.replace(",", " ")
                        
            month_pattern = "|".join(months_map.keys()) 
            date_regex = re.compile(r"(\d{1,2})\s*(?:-\s*(\d{1,2}))?\s+(" + month_pattern + r")\s+(\d{4})")
            match = date_regex.search(full_text)

            if match:
                start_day = int(match.group(1))
                end_day_str = match.group(2) 
                month_str = match.group(3)
                year = int(match.group(4))
                
                month = months_map.get(month_str, 1)

                date_start = datetime.date(year, month, start_day)
                
                if end_day_str:
                    end_day = int(end_day_str)
                    date_end = datetime.date(year, month, end_day)
                else:
                    date_end = date_start            
        except:
            date_start, date_end = None, None

        # SHOW TIME
        show_time = None
        try:
            header_element = driver.find_element(By.CSS_SELECTOR, ".eventDescHeader")
            full_text = header_element.text
            
            time_match = re.search(r'\b(\d{1,2})[:.](\d{2})(?:\s*([APap]\.?[Mm]\.?))?\b', full_text)
            
            if time_match:
                hour = int(time_match.group(1))
                minute = int(time_match.group(2))
                ampm = time_match.group(3) 
                
                if ampm:
                    ampm_clean = ampm.replace(".", "").upper() 
                    
                    if ampm_clean == "PM" and hour != 12:
                        hour += 12
                    elif ampm_clean == "AM" and hour == 12:
                        hour = 0
                
                show_time = f"{hour:02}:{minute:02}:00"
        except:
            show_time = None

        # PRICE MIN & MAX
        price_min, price_max = None, None
        try:
            price_element = WebDriverWait(driver, 10).until(
                EC.visibility_of_element_located(
                    (By.XPATH, "//*[contains(@class, 'eventDescHeader')]//*[contains(text(), 'THB') or contains(text(), 'THB')]")
                )
            )
            price_text = price_element.text

            matches = re.findall(r'[\d,]+', price_text)
            
            prices = []
            for m in matches:
                clean_num = m.replace(",", "")
                
                if clean_num.isdigit():
                    val = int(clean_num)
                    
                    if val > 50 and val not in [2025, 2026, 2027, 2028, 2568, 2569, 2570, 2571]: 
                        prices.append(val)
            
            if prices:
                price_min = min(prices)
                price_max = max(prices)
            
            if price_min == price_max:
                price_max = None
        except:
            price_min, price_max = None, None

        # VENUE NAME && PROVINCE
        venue_name, province = None, None
        try:
            venue_name_element = WebDriverWait(driver, 10).until(
                EC.visibility_of_element_located((By.CSS_SELECTOR, ".eventDescHeader"))
            )
            all_spans = venue_name_element.find_elements(By.TAG_NAME, "span")
            raw_text = all_spans[2].text.strip()

            temp_name = raw_text
            if "อ." in temp_name:
                temp_name = temp_name.split("อ.")[0]
            elif "จ." in temp_name:
                temp_name = temp_name.split("จ.")[0]
            elif "," in temp_name:
                temp_name = temp_name.split(",")[0]

            clean_text = re.sub(r'[,\-\.\(\)]', ' ', temp_name)
            venue_name = re.sub(r'\s+', ' ', clean_text).strip()

            matched_province = None
            raw_text_lower = raw_text.lower()

            for search_term, official_name in province_map.items():
                if search_term in raw_text_lower:
                    matched_province = official_name
                    break 

            province = matched_province
            print(f"Debug Province: Raw='{raw_text}' -> Extracted='{province}'")
        except:
            venue_name = None

        # LATITUDE & LONGITUDE
        latitude, longitude = None, None
        if venue_name:
            print(f"🔎 Geocoding: {venue_name}, {province}...")
            lat, lng = get_coordinates(venue_name, province)
            if lat:
                latitude = lat
                longitude = lng
                print(f"✅ Coordinates Found: {latitude}, {longitude}")
            else:
                print(f"❌ Coordinates Not Found for: {venue_name}")


        # IMAGE
        image_src = None
        
        if name:
            try:
                search_name = name[:15] 
                print(f"🔎 Navigating to category page to fetch poster by name: '{search_name}...'")
                
                driver.get("https://www.allticket.com/category/concert")
                
                time.sleep(3) 
                driver.execute_script("window.scrollTo(0, document.body.scrollHeight / 3);")
                time.sleep(1)
                driver.execute_script("window.scrollTo(0, document.body.scrollHeight / 1.5);")
                time.sleep(2)
                driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
                time.sleep(2)
                
                xpath = f"//*[contains(translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '{search_name.lower()}')]/ancestor::*[(self::a or self::div) and descendant::img][1]/descendant::img"
                
                image_element = WebDriverWait(driver, 10).until(
                    EC.presence_of_element_located((By.XPATH, xpath))
                )
                
                image_src = image_element.get_attribute("src")
                if not image_src:
                    image_src = image_element.get_attribute("srcset")
                    
                print(f"✅ Image Found: {image_src}")
                
            except Exception as e:
                print(f"❌ Could not extract image from category page. The event might be unlisted from the main grid.")
                image_src = None
        else:
            print("❌ No concert name was extracted earlier, skipping category page image search.")

        return {
            "name": name,
            "event_type": "Concert",
            "description": description,
            "picture_url": image_src,
            "start_show_date": str(date_start) if date_start else None,
            "end_show_date": str(date_end) if date_end else None,
            "start_show_time": show_time,
            "price_min": price_min,
            "price_max": price_max,
            "latitude": latitude,
            "longitude": longitude,
            "venue_name": venue_name,
            "province_name": province,
            "ticket_link": url,
            "origin": "All Ticket"
        }

    except Exception as e:
        print(f"Error processing {url}: {e}")
        return None
    finally:
        if driver:
            driver.quit()

def save_concert(data):
    url = "http://127.0.0.1:8000/api/concerts"
    try:
        res = requests.post(url, json=data, timeout=10)
        if res.status_code == 201:
            print(f"Concert '{data.get('name')}' saved successfully. 🎉")
        elif res.status_code == 422:
            try:
                error_data = res.json()
                print(f"Validation Error (422): {error_data}")
            except requests.exceptions.JSONDecodeError:
                print(f"Validation Error (422): Could not decode JSON response.")
        else:
            try:
                message = res.json().get("message", "No message provided")
            except:
                message = res.text
            print(f"Failed to save concert. Status Code {res.status_code}: {message}")

    except requests.exceptions.ConnectionError:
        print(f"Connection Error: Could not connect to API at {url}")
    except requests.exceptions.RequestException as e:
        print(f"An unexpected request error occurred: {e}")

if __name__ == "__main__":
    url = "https://www.allticket.com/event/PliakGonManGwa"
    print(f"🚀 Starting scraper test for: {url}")
    
    data = get_page_destination_data(url, headless=True, timeout=20)

    if data:
        print("\n--- Extracted Data ---")
        for key, value in data.items():
            print(f"{key}: {value}")
        print("----------------------\n")
        # save_concert(data)
    else:
        print("❌ No data extracted or failed to load page.")