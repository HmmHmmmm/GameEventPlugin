## GameEventPlugin

[![](https://poggit.pmmp.io/shield.state/GameEventPlugin)](https://poggit.pmmp.io/p/GameEventPlugin) [![](https://poggit.pmmp.io/shield.api/GameEventPlugin)](https://poggit.pmmp.io/p/GameEventPlugin) [![](https://poggit.pmmp.io/shield.dl.total/GameEventPlugin)](https://poggit.pmmp.io/p/GameEventPlugin)


[Language English](#english)

[Language Thai](#thai)


This plugin uses Libraries `CustomUI` No need to install that plugin
Just load this plugin as .phar


# English

```diff
You must install the plugin
- Slapper
this plugin will work
```

Download the plugin Slapper [Click here](https://poggit.pmmp.io/p/slapper)


**Features of plugin**<br>
- This plugin can create many forms of event. Depends on `command start`
- RunEvent() Will run the present event count down the end, And the event will reset all players who have previously performed this event, And set a new time
- PresentEvent() Is the present event that needs to be run
- NextEvent() Will start a new event after the present event runs to completion (If you set it)
- PlayerStartEvent() Players start the event
- PlayerAwardEvent() Players receive event rewards
- have gui form
- Have language thai and english (You can edit the language you don't like at, `/resources/language`)


**How to use**<br>
- Example of event creation Parkour and BreakBlock https://youtu.be/d6lGZsFmefs

**Command**<br>
- `/gameevent` : open gui form
- `/gameevent create <NameTheEvent> <Time> <DescriptionText> <AwardMessage>` : Create an Event
- `/gameevent list` : See a list of all event.
- `/gameevent remove <EventName>` : Delete event
- `/gameevent settime <EventName> <Time>` : Set event times
- `/gameevent setpresent <EventName>`: Set is the present event
- `/gameevent setnext <EventName>` : Set is the next event
- `/gameevent enabled <EventName> on|off` : Enabled/Disable of event
- `/gameevent start <PlayerName> <EventName>` : Have players start the event
- `/gameevent award <PlayerName> <EventName>` : Add players to receive event rewards.
- `/gameevent sign_start <EventName>` : Create a sign to start an event
- `/gameevent sign_award <EventName>` : Create a sign to award event
- `/gameevent slapper_start <EventName>` : Create a slapper to start an event
- `/gameevent slapper_award <EventName>` : Create a slapper to award event
- `/gameevent welcome <EventName>` : Enabled/Disable Message welcome of event


**Images**<br>
![1](https://github.com/HmmHmmmm/GameEventPlugin/blob/master/images/3.1/1en.jpg)

![2](https://github.com/HmmHmmmm/GameEventPlugin/blob/master/images/3.1/2en.jpg)


# Thai

```diff
คุณต้องลงปลั๊กอิน
- Slapper
ถึงปลั๊กอินนี้จะทำงาน
```

ดาวโหลดปลั๊กอิน Slapper [Click here](https://poggit.pmmp.io/p/slapper)


**คุณสมบัติของปลั๊กอิน**<br>
- ปลั๊กอินนี้สามารถสร้างกิจกรรมได้หลายรูปแบบ ขึ้นอยู่กับคำสั่งสตาร์ทตอนที่สร้างกิจกรรม
- RunEvent() จะรันกิจกรรมปัจจุบันถอยหลังจนจบ และกิจกรรมจะทำการ reset ผู้เล่นที่เคยทำกิจกรรมนี้ทั้งหมด และตั้งเวลาใหม่
- PresentEvent() คือกิจกรรมปัจุบันที่ต้องการให้รัน
- NextEvent() จะเป็นการเริ่มกิจกรรมใหม่จากกิจกรรมปัจุบันที่รันจนจบ (ถ้าคุณเซ็ตไว้)
- PlayerStartEvent() ผู้เล่นเริ่มทำกิจกรรม
- PlayerAwardEvent() ผู้เล่นรับรางวัลกิจกรรม
- มี gui form
- มีภาษา thai english (สามารถแก้ไขภาษาที่คุณไม่ชอบได้ที่ `/resources/language`)


**วิธีใช้งาน**<br>
- ตัวอย่างการสร้างกิจกรรม Parkour และ BreakBlock https://youtu.be/d6lGZsFmefs

**Command**<br>
- `/gameevent` : เปิด gui form
- `/gameevent create <ชื่อกิจกรรม> <เวลา> <ข้อความอธิบาย> <ข้อความรางวัล>` : สร้างกิจกรรม
- `/gameevent list` : ดูรายชื่อกิจกรรม
- `/gameevent remove <ชื่อกิจกรรม>` : ลบกิจกรรม
- `/gameevent settime <ชื่อกิจกรรม> <เวลา>` : เช็ตเวลากิจกรรม
- `/gameevent setpresent <ชื่อกิจกรรม>` : เช็ตเป็นกิจกรรมปัจจุบัน
- `/gameevent setnext <ชื่อกิจกรรม>` : เช็ตเป็นกิจกรรมถัดไป
- `/gameevent enabled <ชื่อกิจกรรม> on|off` : เปิด/ปิดกิจกรรม
- `/gameevent start <ชื่อผู้เล่น> <ชื่อกิจกรรม>` : ให้ผู้เล่นเริ่มทำกิจกรรม
- `/gameevent award <ชื่อผู้เล่น> <ชื่อกิจกรรม>` : เพิ่มผู้เล่นรับรางวัลกิจกรรม
- `/gameevent sign_start <ชื่อกิจกรรม>` : สร้างป้ายเริ่มกิจกรรม
- `/gameevent sign_award <ชื่อกิจกรรม>` : สร้างป้ายรับรางวัลกิจกรรม
- `/gameevent slapper_start <ชื่อกิจกรรม>` : สร้างหุ่นเริ่มกิจกรรม
- `/gameevent slapper_award <ชื่อกิจกรรม>` : สร้างหุ่นกิจกรรมรับรางวัล
- `/gameevent welcome <ชื่อกิจกรรม>` : เปิด/ปิด ข้อความต้อนรับ


**Images**<br>
![1](https://github.com/HmmHmmmm/GameEventPlugin/blob/master/images/3.1/1th.jpg)

![2](https://github.com/HmmHmmmm/GameEventPlugin/blob/master/images/3.1/2th.jpg)



# Config
```
#Language
#thai=ภาษาไทย
#english=English language
language: english
```
  

# Permissions
```
gameevent.command:
  default: op
```
