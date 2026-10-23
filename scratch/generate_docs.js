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

async function generateDocx() {
    const tableHeaderColor = "1E293B"; // Dark slate
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

    const headerCells = [
        "Case ID",
        "Scenario Description",
        "Retail Gross",
        "Mfg Cost",
        "Tariff",
        "Fees/Fundraising",
        "Net Margin",
        "Rep %",
        "Rep Commission",
        "Company Net",
        "Status"
    ].map(title => new TableCell({
        children: [new Paragraph({
            alignment: AlignmentType.CENTER,
            children: [new TextRun({ text: title, bold: true, color: tableHeaderTextColor, size: 18 })]
        })],
        shading: { fill: tableHeaderColor, type: ShadingType.CLEAR },
        borders: borders,
        margins: { top: 120, bottom: 120, left: 100, right: 100 }
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
                        size: 17,
                        color: cellIdx === 8 ? "15803D" : (cellIdx === 6 ? "0F172A" : "334155")
                    })]
                })],
                shading: { fill: bg, type: ShadingType.CLEAR },
                borders: borders,
                margins: { top: 100, bottom: 100, left: 80, right: 80 }
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
                        top: 1000,
                        right: 1000,
                        bottom: 1000,
                        left: 1000
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
                            size: 36,
                            color: "0F172A"
                        })
                    ],
                    spacing: { after: 200 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({
                            text: "Purpose: ",
                            bold: true,
                            size: 22,
                            color: "334155"
                        }),
                        new TextRun({
                            text: "This document serves as the official reference guide and sign-off sheet for verifying the automated financial calculations, rep commissions, deductible fees, and payout lifecycle states prior to production launch.",
                            size: 22,
                            color: "475569"
                        })
                    ],
                    spacing: { after: 250 }
                }),
                new Paragraph({
                    heading: HeadingLevel.HEADING_2,
                    children: [
                        new TextRun({
                            text: "1. Core Calculation Engine Formulas",
                            bold: true,
                            size: 28,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 200, after: 150 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Qualifying Gross Sales = ", bold: true, size: 21 }),
                        new TextRun({ text: "Order Subtotal (excluding customer sales tax and checkout processing fees)", size: 21 })
                    ],
                    spacing: { after: 80 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Total Deductions = ", bold: true, size: 21 }),
                        new TextRun({ text: "Manufacturing Cost (Wholesale) + Tariffs + Platform Fees + Coach/School Fundraising Kickbacks", size: 21 })
                    ],
                    spacing: { after: 80 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Net Margin = ", bold: true, size: 21 }),
                        new TextRun({ text: "max(0, Qualifying Gross Sales - Total Deductions)", size: 21, color: "0284C7" })
                    ],
                    spacing: { after: 80 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Rep Commission Earned = ", bold: true, size: 21 }),
                        new TextRun({ text: "Net Margin × (Rep Commission Rate / 100)", size: 21, color: "16A34A" })
                    ],
                    spacing: { after: 80 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "• Company Retained Share = ", bold: true, size: 21 }),
                        new TextRun({ text: "Net Margin - Rep Commission Earned", size: 21 })
                    ],
                    spacing: { after: 250 }
                }),
                new Paragraph({
                    heading: HeadingLevel.HEADING_2,
                    children: [
                        new TextRun({
                            text: "2. Sample Scenario Matrix",
                            bold: true,
                            size: 28,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 200, after: 150 }
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
                            text: "3. Detailed Walkthrough of Test Scenarios",
                            bold: true,
                            size: 28,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 350, after: 150 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "Case TC-01: Standard Team Store Order\n", bold: true, size: 22, color: "0F172A" }),
                        new TextRun({ text: "• Scenario: Retail gross sales of $1,200.00 with $600.00 manufacturing cost under standard 50/50 split.\n", size: 20 }),
                        new TextRun({ text: "• Math: Margin = $1,200 - $600 = $600.00. Rep Commission (50%) = $300.00. Company Net = $300.00.\n", size: 20 }),
                        new TextRun({ text: "• Expected Status: PENDING (order placed, awaiting production window closing).", size: 20, italic: true })
                    ],
                    spacing: { after: 180 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "Case TC-02: Order with Import Tariff\n", bold: true, size: 22, color: "0F172A" }),
                        new TextRun({ text: "• Scenario: Overseas production run with $150.00 tariff stamped against the batch.\n", size: 20 }),
                        new TextRun({ text: "• Math: Deductions = $1,100 mfg + $150 tariff = $1,250. Margin = $2,500 - $1,250 = $1,250.00. Rep Commission = $625.00.\n", size: 20 }),
                        new TextRun({ text: "• Expected Status: PENDING.", size: 20, italic: true })
                    ],
                    spacing: { after: 180 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "Case TC-03: School Fundraising Kickback\n", bold: true, size: 22, color: "0F172A" }),
                        new TextRun({ text: "• Scenario: School partner negotiated a $300 total fundraising donation rebate.\n", size: 20 }),
                        new TextRun({ text: "• Math: Deductions = $1,400 mfg + $300 fundraising = $1,700. Margin = $3,000 - $1,700 = $1,300.00. Rep Commission = $650.00.\n", size: 20 }),
                        new TextRun({ text: "• Expected Status: CLEARED (ready for bi-weekly payout batch).", size: 20, italic: true })
                    ],
                    spacing: { after: 180 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "Case TC-04 & TC-05: Custom Rep Tiers (Senior 60% & Junior 40%)\n", bold: true, size: 22, color: "0F172A" }),
                        new TextRun({ text: "• TC-04 (60%): Margin of $2,400 yields $1,440.00 rep payout and $960.00 company retained.\n", size: 20 }),
                        new TextRun({ text: "• TC-05 (40%): Margin of $700 yields $280.00 rep payout and $420.00 company retained.", size: 20 })
                    ],
                    spacing: { after: 180 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "Case TC-06: Zero Floor / Negative Margin Protection\n", bold: true, size: 22, color: "B91C1C" }),
                        new TextRun({ text: "• Scenario: High sample costs, re-orders or tariff spikes exceed gross revenue ($480 mfg + $20 tariff + $50 fee = $550 cost vs $500 gross).\n", size: 20 }),
                        new TextRun({ text: "• Math: Unclamped = -$50.00. System clamps via max(0, margin) -> $0.00 net margin. Rep Commission = $0.00.\n", size: 20 }),
                        new TextRun({ text: "• Safety Rule: Rep is never pushed into negative commission balance.", size: 20, italic: true })
                    ],
                    spacing: { after: 250 }
                }),
                new Paragraph({
                    heading: HeadingLevel.HEADING_2,
                    children: [
                        new TextRun({
                            text: "4. Payout Lifecycle Status Definitions",
                            bold: true,
                            size: 28,
                            color: "1E3A8A"
                        })
                    ],
                    spacing: { before: 200, after: 150 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "1. PENDING: ", bold: true, size: 21, color: "D97706" }),
                        new TextRun({ text: "Order placed and paid by customer. Counted in 'Pending Commission' balance. Not yet payable.", size: 21 })
                    ],
                    spacing: { after: 80 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "2. CLEARED: ", bold: true, size: 21, color: "0284C7" }),
                        new TextRun({ text: "Store has closed and entered production without cancellations. Ready for disbursement.", size: 21 })
                    ],
                    spacing: { after: 80 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "3. PAID: ", bold: true, size: 21, color: "16A34A" }),
                        new TextRun({ text: "Admin processes payment. System records paid_to_rep_at timestamp and transfers balance to 'Lifetime Paid Commission'.", size: 21 })
                    ],
                    spacing: { after: 200 }
                }),
                new Paragraph({
                    children: [
                        new TextRun({ text: "Sign-Off: _________________________         Date: _____________", bold: true, size: 22, color: "334155" })
                    ],
                    spacing: { before: 300, after: 100 }
                })
            ]
        }]
    });

    const buffer = await Packer.toBuffer(doc);
    const outputPath = path.resolve(__dirname, '..', 'Commission_Apparel_Financial_Test_Sheet.docx');
    fs.writeFileSync(outputPath, buffer);
    console.log("Created DOCX successfully at:", outputPath);
}

generateDocx().catch(err => {
    console.error("Error generating docx:", err);
    process.exit(1);
});
