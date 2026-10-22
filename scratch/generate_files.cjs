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

async function createDocx() {
    const tableHeaderColor = "1E293B";
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
        "Scenario Description",
        "Retail Gross ($)",
        "Mfg Cost ($)",
        "Tariff ($)",
        "Fees/Fundraising ($)",
        "Net Margin ($)",
        "Rep Rate (%)",
        "Rep Commission ($)",
        "Company Net ($)",
        "Status"
    ];

    const headerCells = headerTitles.map(title => new TableCell({
        children: [new Paragraph({
            alignment: AlignmentType.CENTER,
            children: [new TextRun({ text: title, bold: true, color: tableHeaderTextColor, size: 16 })]
        })],
        shading: { fill: tableHeaderColor, type: ShadingType.CLEAR },
        borders: borders,
        margins: { top: 100, bottom: 100, left: 60, right: 60 }
    }));

    const rowsData = [
        {
            id: "TC-01",
            desc: "Standard Team Store (50/50 split)",
            gross: "$1,200.00",
            mfg: "$600.00",
            tariff: "$0.00",
            fees: "$0.00",
            margin: "$600.00",
            rate: "50.0%",
            rep: "$300.00",
            comp: "$300.00",
            status: "PENDING"
        },
        {
            id: "TC-02",
            desc: "Tariff Applied (Import deduction)",
            gross: "$2,500.00",
            mfg: "$1,100.00",
            tariff: "$150.00",
            fees: "$0.00",
            margin: "$1,250.00",
            rate: "50.0%",
            rep: "$625.00",
            comp: "$625.00",
            status: "PENDING"
        },
        {
            id: "TC-03",
            desc: "School Kickback ($5/item fundraising)",
            gross: "$3,000.00",
            mfg: "$1,400.00",
            tariff: "$0.00",
            fees: "$300.00",
            margin: "$1,300.00",
            rate: "50.0%",
            rep: "$650.00",
            comp: "$650.00",
            status: "CLEARED"
        },
        {
            id: "TC-04",
            desc: "Senior Rep Tier (60% custom rate)",
            gross: "$4,800.00",
            mfg: "$2,200.00",
            tariff: "$100.00",
            fees: "$100.00",
            margin: "$2,400.00",
            rate: "60.0%",
            rep: "$1,440.00",
            comp: "$960.00",
            status: "CLEARED"
        },
        {
            id: "TC-05",
            desc: "Junior Rep Tier (40% custom rate)",
            gross: "$1,500.00",
            mfg: "$750.00",
            tariff: "$0.00",
            fees: "$50.00",
            margin: "$700.00",
            rate: "40.0%",
            rep: "$280.00",
            comp: "$420.00",
            status: "PAID"
        },
        {
            id: "TC-06",
            desc: "Zero-Margin Guard (Costs >= Gross)",
            gross: "$500.00",
            mfg: "$480.00",
            tariff: "$20.00",
            fees: "$50.00",
            margin: "$0.00",
            rate: "50.0%",
            rep: "$0.00",
            comp: "$0.00",
            status: "PENDING"
        },
        {
            id: "TC-07",
            desc: "High-Volume Club Batch (Tariffs & fees)",
            gross: "$8,500.00",
            mfg: "$3,800.00",
            tariff: "$350.00",
            fees: "$250.00",
            margin: "$4,100.00",
            rate: "50.0%",
            rep: "$2,050.00",
            comp: "$2,050.00",
            status: "PAID"
        }
    ];

    const dataRows = rowsData.map((row, idx) => {
        const bg = idx % 2 === 1 ? zebraBg : "FFFFFF";
        const cells = [
            row.id,
            row.desc,
            row.gross,
            row.mfg,
            row.tariff,
            row.fees,
            row.margin,
            row.rate,
            row.rep,
            row.comp,
            row.status
        ];

        return new TableRow({
            children: cells.map((val, cellIdx) => new TableCell({
                children: [new Paragraph({
                    alignment: cellIdx === 0 || cellIdx === 10 ? AlignmentType.CENTER : (cellIdx === 1 ? AlignmentType.LEFT : AlignmentType.RIGHT),
                    children: [new TextRun({
                        text: val,
                        bold: cellIdx === 0 || cellIdx === 6 || cellIdx === 8,
                        size: 16,
                        color: cellIdx === 8 ? "15803D" : (cellIdx === 6 ? "0F172A" : "334155")
                    })]
                })],
                shading: { fill: bg, type: ShadingType.CLEAR },
                borders: borders,
                margins: { top: 80, bottom: 80, left: 60, right: 60 }
            }))
        });
    });

    const doc = new Document({
        styles: {
            default: {
                document: {
                    run: {
                        font: "Calibri"
                    }
                }
            }
        },
        sections: [{
            properties: {
                page: {
                    margin: {
                        top: 900,
                        right: 800,
                        bottom: 900,
                        left: 800
                    }
                }
            },
            children: [
                new Paragraph({
                    heading: HeadingLevel.HEADING_1,
                    children: [
                        new TextRun({
                            text: "Commission Apparel — Financial & Mathematical Test Sheet",
                            bold: true,
                            size: 32,
                            color: "0F172A"
                        })
                    ],
                    spacing: { after: 120 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({
                            text: "Purpose: ",
                            bold: true,
                            size: 20,
                            color: "334155"
                        }),
                        new TextRun({
                            text: "Official testing matrix and calculation sign-off document for platform commission models, wholesale deductions, tariffs, and rep payout statuses prior to live rollout.",
                            size: 20,
                            color: "475569"
                        })
                    ],
                    spacing: { after: 180 }
                }),

                // STAGING SANDBOX CALLOUT
                new Paragraph({
                    children: [
                        new TextRun({
                            text: "⚡ Staging Environment & Stripe Sandbox Testing Instructions",
                            bold: true,
                            size: 22,
                            color: "4338CA"
                        })
                    ],
                    spacing: { before: 100, after: 80 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Gateway Mode: ", bold: true, size: 20 }),
                        new TextRun({ text: "Stripe payments in Staging are configured strictly in Sandbox / Test Mode. No real charges or credit cards are involved.\n", size: 20 }),
                        new TextRun({ text: "• Stripe Test Card: ", bold: true, size: 20 }),
                        new TextRun({ text: "4242 • 4242 • 4242 • 4242\n", bold: true, color: "1E40AF", size: 20 }),
                        new TextRun({ text: "• Expiration Date: ", bold: true, size: 20 }),
                        new TextRun({ text: "Any future date (e.g., 12/28)\n", size: 20 }),
                        new TextRun({ text: "• CVC / Security Code: ", bold: true, size: 20 }),
                        new TextRun({ text: "Any 3 digits (e.g., 123)\n", size: 20 }),
                        new TextRun({ text: "• Billing Postal Code: ", bold: true, size: 20 }),
                        new TextRun({ text: "Any 5 digits (e.g., 90210)\n", size: 20 }),
                        new TextRun({ text: "Use these credentials to simulate live parent orders on storefronts; the checkout webhook will immediately process payment, calculate qualifying sales, and populate the financial deck.", size: 19, italic: true })
                    ],
                    spacing: { after: 220 }
                }),

                new Paragraph({
                    heading: HeadingLevel.HEADING_2,
                    children: [
                        new TextRun({
                            text: "1. Core Mathematical Formula",
                            bold: true,
                            size: 24,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 120, after: 100 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Qualifying Gross Sales = ", bold: true, size: 20 }),
                        new TextRun({ text: "Order Subtotal (excluding customer sales tax & checkout processing fees)", size: 20 })
                    ],
                    spacing: { after: 60 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Net Margin = ", bold: true, size: 20 }),
                        new TextRun({ text: "MAX( 0 , Gross Sales - ( Manufacturing Cost + Tariff + Platform Fees + Fundraising Kickbacks ) )", size: 20, color: "0284C7" })
                    ],
                    spacing: { after: 60 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Rep Commission Earned = ", bold: true, size: 20 }),
                        new TextRun({ text: "Net Margin × ( Rep Commission Rate / 100 )", size: 20, color: "16A34A" })
                    ],
                    spacing: { after: 60 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Company Retained Share = ", bold: true, size: 20 }),
                        new TextRun({ text: "Net Margin - Rep Commission Earned", size: 20 })
                    ],
                    spacing: { after: 200 }
                }),
                new Paragraph({
                    heading: HeadingLevel.HEADING_2,
                    children: [
                        new TextRun({
                            text: "2. Sample Scenario Testing Matrix",
                            bold: true,
                            size: 24,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 150, after: 120 }
                }),
                new Table({
                    rows: [new TableRow({ children: headerCells }), ...dataRows],
                    width: {
                        size: 100,
                        type: WidthType.PERCENTAGE
                    }
                }),
                new Paragraph({
                    heading: HeadingLevel.HEADING_2,
                    children: [
                        new TextRun({
                            text: "3. Walkthrough & Guard Rails",
                            bold: true,
                            size: 24,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 250, after: 100 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "TC-01 (Standard Store): ", bold: true, size: 20 }),
                        new TextRun({ text: "$1,200 Gross - $600 Mfg = $600 Margin. At 50%, Rep receives $300, Company receives $300.\n", size: 20 }),
                        new TextRun({ text: "TC-02 (Tariff Deduction): ", bold: true, size: 20 }),
                        new TextRun({ text: "$2,500 Gross - ($1,100 Mfg + $150 Tariff) = $1,250 Margin. Rep receives $625.\n", size: 20 }),
                        new TextRun({ text: "TC-03 (School Fundraising): ", bold: true, size: 20 }),
                        new TextRun({ text: "$3,000 Gross - ($1,400 Mfg + $300 Kickback) = $1,300 Margin. Rep receives $650.\n", size: 20 }),
                        new TextRun({ text: "TC-04 & TC-05 (Custom Tiers): ", bold: true, size: 20 }),
                        new TextRun({ text: "Senior reps (60%) and Junior/Referral reps (40%) correctly compute split based on individual profile rate setting.\n", size: 20 }),
                        new TextRun({ text: "TC-06 (Zero Floor Guard): ", bold: true, size: 20, color: "DC2626" }),
                        new TextRun({ text: "When costs ($550) exceed gross ($500), Net Margin clamps to $0.00. The agent account is never placed into negative balance.", size: 20 })
                    ],
                    spacing: { after: 200 }
                }),
                new Paragraph({
                    heading: HeadingLevel.HEADING_2,
                    children: [
                        new TextRun({
                            text: "4. Payout Status Progression",
                            bold: true,
                            size: 24,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 150, after: 100 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• PENDING: ", bold: true, size: 20, color: "D97706" }),
                        new TextRun({ text: "Customer payment confirmed. Order awaiting store closing / manufacturing lock.\n", size: 20 }),
                        new TextRun({ text: "• CLEARED: ", bold: true, size: 20, color: "0284C7" }),
                        new TextRun({ text: "Store locked & verified. Eligible for payout in next scheduled payout run.\n", size: 20 }),
                        new TextRun({ text: "• PAID: ", bold: true, size: 20, color: "16A34A" }),
                        new TextRun({ text: "Admin has released payout. Timestamped with paid_to_rep_at and added to lifetime earnings.", size: 20 })
                    ],
                    spacing: { after: 250 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "Approved By: ____________________________________     Date: ____________________", bold: true, size: 20, color: "334155" })
                    ],
                    spacing: { before: 200, after: 50 }
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
        ["Commission Apparel - Financial & Mathematical Test Matrix", "", "", "", "", "", "", "", "", "", ""],
        ["Formulas used: Net Margin = MAX(0, Gross - (Mfg + Tariff + Fees)), Rep Earned = Margin * Rate, Company Net = Margin - Rep Earned", "", "", "", "", "", "", "", "", "", ""],
        ["⚡ NOTE: Staging Stripe is in Sandbox Mode. Use Test Card: 4242 4242 4242 4242 | Exp: Any future (e.g. 12/28) | CVC: 123 | Zip: 90210", "", "", "", "", "", "", "", "", "", ""],
        [],
        [
            "Case ID",
            "Scenario Description",
            "Retail Gross ($)",
            "Mfg Cost ($)",
            "Tariff ($)",
            "Fees / Fundraising ($)",
            "Net Margin ($)",
            "Rep Rate",
            "Rep Commission ($)",
            "Company Net ($)",
            "Payout Status"
        ],
        // Row 6: TC-01
        ["TC-01", "Standard Team Store (50/50 split)", 1200.00, 600.00, 0.00, 0.00, { f: "MAX(0, C6-(D6+E6+F6))" }, 0.50, { f: "G6*H6" }, { f: "G6-I6" }, "PENDING"],
        // Row 7: TC-02
        ["TC-02", "Tariff Applied (Import deduction)", 2500.00, 1100.00, 150.00, 0.00, { f: "MAX(0, C7-(D7+E7+F7))" }, 0.50, { f: "G7*H7" }, { f: "G7-I7" }, "PENDING"],
        // Row 8: TC-03
        ["TC-03", "School Kickback ($5/item fundraising)", 3000.00, 1400.00, 0.00, 300.00, { f: "MAX(0, C8-(D8+E8+F8))" }, 0.50, { f: "G8*H8" }, { f: "G8-I8" }, "CLEARED"],
        // Row 9: TC-04
        ["TC-04", "Senior Rep Tier (60% custom rate)", 4800.00, 2200.00, 100.00, 100.00, { f: "MAX(0, C9-(D9+E9+F9))" }, 0.60, { f: "G9*H9" }, { f: "G9-I9" }, "CLEARED"],
        // Row 10: TC-05
        ["TC-05", "Junior Rep Tier (40% custom rate)", 1500.00, 750.00, 0.00, 50.00, { f: "MAX(0, C10-(D10+E10+F10))" }, 0.40, { f: "G10*H10" }, { f: "G10-I10" }, "PAID"],
        // Row 11: TC-06
        ["TC-06", "Zero-Margin Guard (Costs >= Gross)", 500.00, 480.00, 20.00, 50.00, { f: "MAX(0, C11-(D11+E11+F11))" }, 0.50, { f: "G11*H11" }, { f: "G11-I11" }, "PENDING"],
        // Row 12: TC-07
        ["TC-07", "High-Volume Club Batch (Tariffs & fees)", 8500.00, 3800.00, 350.00, 250.00, { f: "MAX(0, C12-(D12+E12+F12))" }, 0.50, { f: "G12*H12" }, { f: "G12-I12" }, "PAID"],
        [],
        // Row 14: Totals
        ["TOTALS", "Portfolio Aggregates", { f: "SUM(C6:C12)" }, { f: "SUM(D6:D12)" }, { f: "SUM(E6:E12)" }, { f: "SUM(F6:F12)" }, { f: "SUM(G6:G12)" }, "—", { f: "SUM(I6:I12)" }, { f: "SUM(J6:J12)" }, "—"]
    ];

    const ws = XLSX.utils.aoa_to_sheet(wsData);

    ws['!cols'] = [
        { wch: 10 },
        { wch: 40 },
        { wch: 16 },
        { wch: 14 },
        { wch: 12 },
        { wch: 22 },
        { wch: 16 },
        { wch: 12 },
        { wch: 20 },
        { wch: 18 },
        { wch: 14 }
    ];

    XLSX.utils.book_append_sheet(wb, ws, "Financial Test Sheet");

    const outputPath = path.resolve(__dirname, '..', 'Commission_Apparel_Financial_Test_Sheet.xlsx');
    const buf = XLSX.write(wb, { type: 'buffer', bookType: 'xlsx' });
    safeWrite(outputPath, buf);
}

async function main() {
    await createDocx();
    createXlsx();
}

main().catch(console.error);
