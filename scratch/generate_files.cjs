const fs = require('fs');
const path = require('path');
const {
    Document,
    Packer,
    Paragraph,
    TextRun,
    Table,
    TableRow,
    TableCell,
    WidthType,
    BorderStyle,
    HeadingLevel,
    AlignmentType,
    ShadingType
} = require('docx');
const XLSX = require('xlsx');

function safeWrite(filePath, buffer) {
    try {
        fs.writeFileSync(filePath, buffer);
        console.log("Successfully wrote to:", filePath);
        return filePath;
    } catch (err) {
        if (err.code === 'EBUSY') {
            const ext = path.extname(filePath);
            const base = path.basename(filePath, ext);
            const altPath = path.join(path.dirname(filePath), `${base}_Updated${ext}`);
            fs.writeFileSync(altPath, buffer);
            console.log(`Original file was open in Word/Excel. Wrote updated copy to: ${altPath}`);
            return altPath;
        }
        throw err;
    }
}

// Exact 7 scenarios from FinancialTestScenarioSeeder.php
const scenarios = [
    {
        code: "TC-01",
        title: "Standard Team Store (50/50 split)",
        athlete: "Marcus Vance",
        item: "Custom Sublimated Team Uniform Set",
        gross: 1200.00,
        mfg: 600.00,
        tariff: 0.00,
        fee: 0.00,
        fundraising: 0.00,
        margin: 600.00,
        rate: 0.50,
        rateStr: "50.0%",
        rep: 300.00,
        comp: 300.00,
        status: "PENDING",
        notes: "Clean 50/50 split on standard team store order."
    },
    {
        code: "TC-02",
        title: "Tariff Applied (Import deduction)",
        athlete: "Jordan Hayes",
        item: "Overseas Custom Sublimation Package",
        gross: 2500.00,
        mfg: 1100.00,
        tariff: 150.00,
        fee: 0.00,
        fundraising: 0.00,
        margin: 1250.00,
        rate: 0.50,
        rateStr: "50.0%",
        rep: 625.00,
        comp: 625.00,
        status: "PENDING",
        notes: "Custom overseas sublimation with $150 tariff deduction."
    },
    {
        code: "TC-03",
        title: "School Kickback ($5/item fundraising)",
        athlete: "Lucas Reyes",
        item: "Varsity Fan Wear & Uniform Combo",
        gross: 3000.00,
        mfg: 1400.00,
        tariff: 0.00,
        fee: 0.00,
        fundraising: 300.00,
        margin: 1300.00,
        rate: 0.50,
        rateStr: "50.0%",
        rep: 650.00,
        comp: 650.00,
        status: "CLEARED",
        notes: "School booster club fundraising kickback of $300 deducted."
    },
    {
        code: "TC-04",
        title: "Senior Rep Tier (60% custom rate)",
        athlete: "Devon Miller",
        item: "Championship Club Pack (Senior Tier)",
        gross: 4800.00,
        mfg: 2200.00,
        tariff: 100.00,
        fee: 100.00,
        fundraising: 0.00,
        margin: 2400.00,
        rate: 0.60,
        rateStr: "60.0%",
        rep: 1440.00,
        comp: 960.00,
        status: "CLEARED",
        notes: "Senior partner commission tier override at 60.0%."
    },
    {
        code: "TC-05",
        title: "Junior Rep Tier (40% custom rate)",
        athlete: "Ethan Cruz",
        item: "Youth League Practice Pack (Junior Tier)",
        gross: 1500.00,
        mfg: 750.00,
        tariff: 0.00,
        fee: 50.00,
        fundraising: 0.00,
        margin: 700.00,
        rate: 0.40,
        rateStr: "40.0%",
        rep: 280.00,
        comp: 420.00,
        status: "PAID",
        notes: "Junior partner referral tier at 40.0%, fully disbursed."
    },
    {
        code: "TC-06",
        title: "Zero-Margin Guard (Costs >= Gross)",
        athlete: "Liam Walker",
        item: "Rushed Prototype Sample Kit",
        gross: 500.00,
        mfg: 480.00,
        tariff: 20.00,
        fee: 50.00,
        fundraising: 0.00,
        margin: 0.00,
        rate: 0.50,
        rateStr: "50.0%",
        rep: 0.00,
        comp: 0.00,
        status: "PENDING",
        notes: "High sample cost guard test ($550 total costs vs $500 gross). Clamped to $0.00."
    },
    {
        code: "TC-07",
        title: "High-Volume Club Batch (Tariffs & fees)",
        athlete: "Tyler Brooke",
        item: "Regional Travel Program Elite Batch",
        gross: 8500.00,
        mfg: 3800.00,
        tariff: 350.00,
        fee: 250.00,
        fundraising: 0.00,
        margin: 4100.00,
        rate: 0.50,
        rateStr: "50.0%",
        rep: 2050.00,
        comp: 2050.00,
        status: "PAID",
        notes: "High volume travel club batch, fully cleared and paid out."
    }
];

async function createDocx() {
    const tableHeaderColor = "0F172A"; // Slate 900
    const tableHeaderTextColor = "FFFFFF";
    const zebraBg = "F8FAFC";
    const borderColor = "CBD5E1";

    const borderStyle = {
        style: BorderStyle.SINGLE,
        size: 1,
        color: borderColor
    };
    const borders = {
        top: borderStyle,
        bottom: borderStyle,
        left: borderStyle,
        right: borderStyle
    };

    const headerTitles = [
        "Case ID",
        "Athlete / Seed Item",
        "Scenario Type",
        "Gross ($)",
        "Mfg ($)",
        "Tariff ($)",
        "Deductions ($)",
        "Margin ($)",
        "Rep %",
        "Rep Earned ($)",
        "Status"
    ];

    const headerCells = headerTitles.map(title => new TableCell({
        children: [new Paragraph({
            alignment: AlignmentType.CENTER,
            children: [new TextRun({ text: title, bold: true, color: tableHeaderTextColor, size: 15 })]
        })],
        shading: { fill: tableHeaderColor, type: ShadingType.CLEAR },
        borders: borders,
        margins: { top: 100, bottom: 100, left: 50, right: 50 }
    }));

    const dataRows = scenarios.map((sc, idx) => {
        const bg = idx % 2 === 1 ? zebraBg : "FFFFFF";
        const totalDeductions = sc.fee + sc.fundraising;

        const cells = [
            sc.code,
            `${sc.athlete}\n(${sc.item})`,
            sc.title,
            `$${sc.gross.toFixed(2)}`,
            `$${sc.mfg.toFixed(2)}`,
            `$${sc.tariff.toFixed(2)}`,
            `$${totalDeductions.toFixed(2)}`,
            `$${sc.margin.toFixed(2)}`,
            sc.rateStr,
            `$${sc.rep.toFixed(2)}`,
            sc.status
        ];

        return new TableRow({
            children: cells.map((val, cellIdx) => new TableCell({
                children: [new Paragraph({
                    alignment: cellIdx === 0 || cellIdx === 8 || cellIdx === 10 ? AlignmentType.CENTER : (cellIdx === 1 || cellIdx === 2 ? AlignmentType.LEFT : AlignmentType.RIGHT),
                    children: [new TextRun({
                        text: val,
                        bold: cellIdx === 0 || cellIdx === 7 || cellIdx === 9,
                        size: 15,
                        color: cellIdx === 9 ? "15803D" : (cellIdx === 7 ? "0F172A" : (cellIdx === 10 && val === 'PAID' ? "16A34A" : (cellIdx === 10 && val === 'CLEARED' ? "0284C7" : "334155")))
                    })]
                })],
                shading: { fill: bg, type: ShadingType.CLEAR },
                borders: borders,
                margins: { top: 80, bottom: 80, left: 50, right: 50 }
            }))
        });
    });

    const doc = new Document({
        styles: {
            default: {
                document: {
                    run: { font: "Calibri" }
                }
            }
        },
        sections: [{
            properties: {
                page: {
                    margin: { top: 800, right: 700, bottom: 800, left: 700 }
                }
            },
            children: [
                new Paragraph({
                    heading: HeadingLevel.HEADING_1,
                    children: [
                        new TextRun({
                            text: "Commission Apparel — Financial & Mathematical Test Verification Sheet",
                            bold: true,
                            size: 30,
                            color: "0F172A"
                        })
                    ],
                    spacing: { after: 120 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "Aligned with Staging Database Seeder: ", bold: true, size: 20, color: "1E3A8A" }),
                        new TextRun({ text: "FinancialTestScenarioSeeder.php", italics: true, size: 20, color: "0284C7" })
                    ],
                    spacing: { after: 140 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({
                            text: "This document is 1-to-1 aligned with the automated staging test seeder. When you or Luke view the Sales Partner Control Deck or Super Admin Financials on Staging, the numbers on your screen must match the tables and dashboard summary cards below down to the penny.",
                            size: 19,
                            color: "475569"
                        })
                    ],
                    spacing: { after: 200 }
                }),

                // STAGING CARD TARGET SUMMARY
                new Paragraph({
                    heading: HeadingLevel.HEADING_2,
                    children: [
                        new TextRun({
                            text: "1. Expected Staging Control Deck Summary Cards",
                            bold: true,
                            size: 24,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 120, after: 100 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Pending Commission: ", bold: true, size: 20, color: "D97706" }),
                        new TextRun({ text: "$925.00  ", bold: true, size: 22, color: "D97706" }),
                        new TextRun({ text: "(TC-01: $300.00 + TC-02: $625.00 + TC-06: $0.00)\n", size: 19, color: "64748B" }),

                        new TextRun({ text: "• Cleared Commission: ", bold: true, size: 20, color: "0284C7" }),
                        new TextRun({ text: "$2,090.00  ", bold: true, size: 22, color: "0284C7" }),
                        new TextRun({ text: "(TC-03: $650.00 + TC-04: $1,440.00)\n", size: 19, color: "64748B" }),

                        new TextRun({ text: "• Paid Commission: ", bold: true, size: 20, color: "16A34A" }),
                        new TextRun({ text: "$2,330.00  ", bold: true, size: 22, color: "16A34A" }),
                        new TextRun({ text: "(TC-05: $280.00 + TC-07: $2,050.00)\n", size: 19, color: "64748B" }),

                        new TextRun({ text: "• Lifetime Commission Earned: ", bold: true, size: 20, color: "0F172A" }),
                        new TextRun({ text: "$5,345.00\n", bold: true, size: 22, color: "0F172A" }),

                        new TextRun({ text: "• Total Qualifying Sales: ", bold: true, size: 20 }),
                        new TextRun({ text: "$22,000.00   |   ", size: 20 }),
                        new TextRun({ text: "• Total Net Margin: ", bold: true, size: 20 }),
                        new TextRun({ text: "$10,350.00   |   ", size: 20 }),
                        new TextRun({ text: "• Company Retained Share: ", bold: true, size: 20 }),
                        new TextRun({ text: "$5,005.00", size: 20 })
                    ],
                    spacing: { after: 220 }
                }),

                // STRIPE SANDBOX NOTICE
                new Paragraph({
                    children: [
                        new TextRun({
                            text: "⚡ Staging Stripe Sandbox Test Credentials",
                            bold: true,
                            size: 21,
                            color: "4338CA"
                        })
                    ],
                    spacing: { before: 80, after: 60 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "Staging payments are locked into Stripe Test Mode. For any additional storefront checkout testing:\n", size: 19 }),
                        new TextRun({ text: "• Card Number: ", bold: true, size: 19 }),
                        new TextRun({ text: "4242 • 4242 • 4242 • 4242   ", bold: true, color: "1E40AF", size: 19 }),
                        new TextRun({ text: "• Expiration: ", bold: true, size: 19 }),
                        new TextRun({ text: "Any future date (12/28)   ", size: 19 }),
                        new TextRun({ text: "• CVC: ", bold: true, size: 19 }),
                        new TextRun({ text: "123   ", size: 19 }),
                        new TextRun({ text: "• Zip: ", bold: true, size: 19 }),
                        new TextRun({ text: "90210", size: 19 })
                    ],
                    spacing: { after: 200 }
                }),

                // TABLE
                new Paragraph({
                    heading: HeadingLevel.HEADING_2,
                    children: [
                        new TextRun({
                            text: "2. Seeder Scenario Matrix (TC-01 through TC-07)",
                            bold: true,
                            size: 24,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 120, after: 120 }
                }),
                new Table({
                    rows: [new TableRow({ children: headerCells }), ...dataRows],
                    width: { size: 100, type: WidthType.PERCENTAGE }
                }),

                // SCENARIO AUDIT DETAILS
                new Paragraph({
                    heading: HeadingLevel.HEADING_2,
                    children: [
                        new TextRun({
                            text: "3. Seeder Transaction Verification Details",
                            bold: true,
                            size: 24,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 250, after: 100 }
                }),
                ...scenarios.map(sc => new Paragraph({
                    children: [
                        new TextRun({ text: `[${sc.code}] ${sc.title}\n`, bold: true, size: 20, color: "0F172A" }),
                        new TextRun({ text: `• Athlete / Order Contact: `, bold: true, size: 18 }),
                        new TextRun({ text: `${sc.athlete} (${sc.athlete.toLowerCase().replace(' ', '.')}@example.com)\n`, size: 18 }),
                        new TextRun({ text: `• Seed Item: `, bold: true, size: 18 }),
                        new TextRun({ text: `${sc.item}\n`, size: 18 }),
                        new TextRun({ text: `• Calculation: `, bold: true, size: 18 }),
                        new TextRun({ text: `Gross ($${sc.gross.toFixed(2)}) - [Mfg ($${sc.mfg.toFixed(2)}) + Tariff ($${sc.tariff.toFixed(2)}) + Deductions ($${(sc.fee + sc.fundraising).toFixed(2)})] = Net Margin of $${sc.margin.toFixed(2)}.\n`, size: 18 }),
                        new TextRun({ text: `• Commission Payout: `, bold: true, size: 18 }),
                        new TextRun({ text: `$${sc.margin.toFixed(2)} × ${sc.rateStr} = $${sc.rep.toFixed(2)} rep earned. Company retained share = $${sc.comp.toFixed(2)}.\n`, size: 18, color: "15803D" }),
                        new TextRun({ text: `• Target Status: `, bold: true, size: 18 }),
                        new TextRun({ text: `${sc.status} | Note: ${sc.notes}`, size: 18, italics: true })
                    ],
                    spacing: { after: 150 }
                })),

                // SIGN OFF
                new Paragraph({
                    children: [
                        new TextRun({ text: "Mathematical Audit Sign-Off: ____________________________       Date: __________________", bold: true, size: 20, color: "334155" })
                    ],
                    spacing: { before: 250, after: 50 }
                })
            ]
        }]
    });

    const buffer = await Packer.toBuffer(doc);
    const outputPath = path.resolve(__dirname, '..', 'Commission_Apparel_Financial_Test_Sheet.docx');
    safeWrite(outputPath, buffer);
}

function createXlsx() {
    const wb = XLSX.utils.book_new();

    const wsData = [
        ["Commission Apparel - Financial & Mathematical Test Matrix (Aligned with Staging Seeder)", "", "", "", "", "", "", "", "", "", "", ""],
        ["Seeder File: database/seeders/FinancialTestScenarioSeeder.php", "", "", "", "", "", "", "", "", "", "", ""],
        ["Formula Engine: Net Margin = MAX(0, Gross - (Mfg + Tariff + Deductions)) | Rep Earned = Margin * Rate | Company Net = Margin - Rep Earned", "", "", "", "", "", "", "", "", "", "", ""],
        ["⚡ Staging Stripe Sandbox Card: 4242 4242 4242 4242 | Exp: Any future date (12/28) | CVC: 123 | Zip: 90210", "", "", "", "", "", "", "", "", "", "", ""],
        [],
        [
            "Case ID",
            "Athlete Name",
            "Order Item Name",
            "Scenario Description",
            "Retail Gross ($)",
            "Mfg Cost ($)",
            "Tariff ($)",
            "Fees / Fundraising ($)",
            "Net Margin ($)",
            "Rep Rate",
            "Rep Commission ($)",
            "Company Net ($)",
            "Payout Status",
            "Seeder Notes"
        ],
        // Rows 7 to 13
        ...scenarios.map((sc, i) => {
            const rowIdx = 7 + i;
            return [
                sc.code,
                sc.athlete,
                sc.item,
                sc.title,
                sc.gross,
                sc.mfg,
                sc.tariff,
                sc.fee + sc.fundraising,
                { f: `MAX(0, E${rowIdx}-(F${rowIdx}+G${rowIdx}+H${rowIdx}))` },
                sc.rate,
                { f: `I${rowIdx}*J${rowIdx}` },
                { f: `I${rowIdx}-K${rowIdx}` },
                sc.status,
                sc.notes
            ];
        }),
        [],
        // Row 15: Totals
        [
            "TOTALS",
            "—",
            "—",
            "Portfolio Aggregates",
            { f: "SUM(E7:E13)" },
            { f: "SUM(F7:F13)" },
            { f: "SUM(G7:G13)" },
            { f: "SUM(H7:H13)" },
            { f: "SUM(I7:I13)" },
            "—",
            { f: "SUM(K7:K13)" },
            { f: "SUM(L7:L13)" },
            "—",
            "—"
        ],
        [],
        ["STAGING DASHBOARD SUMMARY CARDS CHECKLIST", "", "", "", "", "", "", "", "", "", "", ""],
        ["Metric Card", "Expected Target", "Seeder Test Cases Included", "", "", "", "", "", "", "", "", ""],
        ["Pending Commission", 925.00, "TC-01 ($300) + TC-02 ($625) + TC-06 ($0)", "", "", "", "", "", "", "", "", ""],
        ["Cleared Commission", 2090.00, "TC-03 ($650) + TC-04 ($1,440)", "", "", "", "", "", "", "", "", ""],
        ["Paid Commission", 2330.00, "TC-05 ($280) + TC-07 ($2,050)", "", "", "", "", "", "", "", "", ""],
        ["Lifetime Commission Earned", 5345.00, "Sum of all 7 test cases", "", "", "", "", "", "", "", "", ""]
    ];

    const ws = XLSX.utils.aoa_to_sheet(wsData);

    ws['!cols'] = [
        { wch: 10 }, // Case ID
        { wch: 16 }, // Athlete
        { wch: 38 }, // Item
        { wch: 36 }, // Scenario Description
        { wch: 15 }, // Gross
        { wch: 13 }, // Mfg
        { wch: 11 }, // Tariff
        { wch: 20 }, // Fees / Fundraising
        { wch: 15 }, // Net Margin
        { wch: 11 }, // Rate
        { wch: 19 }, // Rep Commission
        { wch: 17 }, // Company Net
        { wch: 14 }, // Status
        { wch: 50 }  // Notes
    ];

    XLSX.utils.book_append_sheet(wb, ws, "Financial Test Matrix");

    const outputPath = path.resolve(__dirname, '..', 'Commission_Apparel_Financial_Test_Sheet.xlsx');
    const buf = XLSX.write(wb, { type: 'buffer', bookType: 'xlsx' });
    safeWrite(outputPath, buf);
}

async function main() {
    await createDocx();
    createXlsx();
}

main().catch(console.error);
