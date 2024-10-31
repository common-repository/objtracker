DROP PROCEDURE IF EXISTS objtrackerP_Audit_Define_Table;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Define_Column;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Add_auditindex;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Varchar;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Bit;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Delete_bit;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Bit2;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_BitChar;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Delete_bitChar;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Char;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Delete_Char;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Bit2Char;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Datetime;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Datetime2;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Delete_DateTime;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Int;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Delete_int;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Int2;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_Show_changes;
DROP FUNCTION IF EXISTS objtrackerF_FiscalYear;
DROP FUNCTION IF EXISTS objtrackerF_FiscalYearOfDate;
DROP FUNCTION IF EXISTS objtrackerF_FormatDate;
DROP FUNCTION IF EXISTS objtrackerF_FormatFiscalYear;
DROP FUNCTION IF EXISTS objtrackerF_FormatSortedDate;
DROP FUNCTION IF EXISTS objtrackerF_Status;
DROP FUNCTION IF EXISTS objtrackerF_StatusCompare;
DROP FUNCTION IF EXISTS objtrackerF_StatusDate;
DROP FUNCTION IF EXISTS objtrackerF_StatusDecimal;
DROP FUNCTION IF EXISTS objtrackerF_StatusDollar;
DROP FUNCTION IF EXISTS objtrackerF_StatusInteger;
DROP FUNCTION IF EXISTS objtrackerF_StatusPercent;
DROP FUNCTION IF EXISTS objtrackerF_StatusRatio;
DROP FUNCTION IF EXISTS objtrackerF_TrueFalse;
DROP PROCEDURE IF EXISTS objtrackerP_AlertPrompt;
DROP PROCEDURE IF EXISTS objtrackerP_Alerts;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_DocumentColumn;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_DocumentTable;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_DocumentTables;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_IndexItem;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_IndexList;
DROP PROCEDURE IF EXISTS objtrackerP_Audit_ItemList;
DROP PROCEDURE IF EXISTS objtrackerP_Baseline;
DROP PROCEDURE IF EXISTS objtrackerP_Dashboard;
DROP PROCEDURE IF EXISTS objtrackerP_DefaultPasswordUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_DepartmentDelete;
DROP PROCEDURE IF EXISTS objtrackerP_DepartmentInsert;
DROP PROCEDURE IF EXISTS objtrackerP_DepartmentList;
DROP PROCEDURE IF EXISTS objtrackerP_DepartmentUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_Documentation;
DROP PROCEDURE IF EXISTS objtrackerP_DocumentationDelete;
DROP PROCEDURE IF EXISTS objtrackerP_DocumentationInsert;
DROP PROCEDURE IF EXISTS objtrackerP_DocumentationList;
DROP PROCEDURE IF EXISTS objtrackerP_ExtendList;
DROP PROCEDURE IF EXISTS objtrackerP_ExtendUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_Extract;
DROP PROCEDURE IF EXISTS objtrackerP_FiscalYear2List;
DROP PROCEDURE IF EXISTS objtrackerP_FiscalYearDelete;
DROP PROCEDURE IF EXISTS objtrackerP_FiscalYearInsert;
DROP PROCEDURE IF EXISTS objtrackerP_FiscalYearList;
DROP PROCEDURE IF EXISTS objtrackerP_FiscalYearUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_FrequencyList;
DROP PROCEDURE IF EXISTS objtrackerP_FrequencyUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_MeasurementDelete;
DROP PROCEDURE IF EXISTS objtrackerP_MeasurementInsert;
DROP PROCEDURE IF EXISTS objtrackerP_MeasurementList;
DROP PROCEDURE IF EXISTS objtrackerP_MeasurementUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_MeasurementsMissing;
DROP PROCEDURE IF EXISTS objtrackerP_MetricTypeList;
DROP PROCEDURE IF EXISTS objtrackerP_MetricTypeUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_Objective;
DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveDelete;
DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveInsert;
DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveList;
DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveTypeList;
DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveTypeUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_ObjectiveUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_OrganizationList;
DROP PROCEDURE IF EXISTS objtrackerP_OrganizationUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_OrganizationUpdateB;
DROP PROCEDURE IF EXISTS objtrackerP_PersonCheck;
DROP PROCEDURE IF EXISTS objtrackerP_PersonDelete;
DROP PROCEDURE IF EXISTS objtrackerP_PersonFactsGet;
DROP PROCEDURE IF EXISTS objtrackerP_PersonInsert;
DROP PROCEDURE IF EXISTS objtrackerP_PersonList;
DROP PROCEDURE IF EXISTS objtrackerP_PersonPasswordReset;
DROP PROCEDURE IF EXISTS objtrackerP_PersonPasswordUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_PersonUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_PersonUpdateUI;
DROP PROCEDURE IF EXISTS objtrackerP_ReportMeasurements;
DROP PROCEDURE IF EXISTS objtrackerP_Org0Init;
DROP PROCEDURE IF EXISTS objtrackerP_OrgDelete;
DROP PROCEDURE IF EXISTS objtrackerP_OrgNInit;
DROP PROCEDURE IF EXISTS objtrackerP_OrgInsert;
DROP PROCEDURE IF EXISTS objtrackerP_OrgList;
DROP PROCEDURE IF EXISTS objtrackerP_OrgUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_Sub_ExtendFY;
DROP PROCEDURE IF EXISTS objtrackerP_TargetObjList;
DROP PROCEDURE IF EXISTS objtrackerP_TargetObjUpdate;
DROP PROCEDURE IF EXISTS objtrackerP_Usage;
DROP PROCEDURE IF EXISTS objtrackerP_UsageTitle;
DROP TABLE IF EXISTS objtrackerT_Documentation ,
 objtrackerT_InstallState ,
 objtrackerT_Measurement ,
 objtrackerT_Target ,
 objtrackerT_Objective ,
 objtrackerT_Person ,
 objtrackerT_Department,
 objtrackerT_ObjectiveType ,
 objtrackerT_Frequency ,
 objtrackerT_MetricType ,
 objtrackerT_FiscalYear,
 objtrackerT_FyCalendar,
 objtrackerT_AuditIndex,
 objtrackerT_Audit,
 objtrackerT_AuditColumn,
 objtrackerT_AuditTable,
 objtrackerT_Organization ;
