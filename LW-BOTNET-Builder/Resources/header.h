#pragma once
 #include <Windows.h>
 #include <wininet.h>  
 #include <Shlwapi.h> 
 #include <urlmon.h>  
 #include <iomanip> 
 #include <sstream> 
 #include <iostream> 
 #include <string> 
 #include <vector> 
 #include "libs/json/json.h" 
 #include "libs/jsoncpp.cpp" 
 #pragma comment(lib, "wininet.lib") 
 #pragma comment(lib, "shlwapi.lib") 
 #pragma comment(lib, "urlmon.lib") 
 #define _WIN32_WINNT 0x0400 
 Json::Reader reader; 
 Json::Value root; 
 using namespace std; 
 #include <commdlg.h>