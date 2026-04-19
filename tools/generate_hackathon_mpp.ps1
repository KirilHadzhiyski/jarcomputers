$ErrorActionPreference = 'Stop'

$outputPath = 'C:\Users\PC\Desktop\New folder (7)\AUBG_Hackathon_Course_Project_Plan.mpp'
$projectStart = Get-Date '2026-05-04 08:00'
$contingencyReserve = 2899.60
$managementReserve = 1449.80

$resourceRows = @'
Name|Rate|MaxUnits
Project Manager|25|1.25
Marketing Lead|20|1.0
Sponsorship Manager|22|1.0
Event Coordinator|18|1.25
Logistics Coordinator|18|1.25
Technical Support|20|1.25
'@ | ConvertFrom-Csv -Delimiter '|'

$taskRows = @'
Key|Level|Name|Duration|Resource|Units|FixedCost|Milestone|Predecessors|Deadline|Notes
1|1|1. Initiation & Planning||||0|FALSE|||Phase summary from the labor-cost workbook.
1.1|2|Define event concept|2d|Project Manager|1.0|0|FALSE|||Initial concept framing.
1.2|2|Draft business case and objectives|1d|Project Manager|1.0|0|FALSE|1.1||Business justification and success criteria.
1.3|2|Identify stakeholders|2d|Project Manager|1.0|0|FALSE|1.2||Stakeholder mapping and influence review.
1.4|2|Define scope assumptions and risk approach|2d|Project Manager|1.0|0|FALSE|1.3||Scope statement, assumptions, and initial risk framing.
1.5|2|Build WBS and schedule baseline logic|2d|Project Manager|1.0|0|FALSE|1.4||Detailed planning structure for schedule and cost integration.
1.6|2|Prepare kickoff package and approvals|1d|Project Manager|1.0|0|FALSE|1.5||Planning package prepared for sign-off.
1.M1|2|Planning approved|0d|||0|TRUE|1.6|2026-05-15 17:00|Gate milestone for downstream work.
2|1|2. Sponsorship & Budgeting||||0|FALSE|||Phase summary from the labor-cost workbook.
2.1|2|Build sponsor target list|2d|Sponsorship Manager|1.0|0|FALSE|1.M1||Initial sponsor prospecting.
2.2|2|Prioritize sponsor outreach list|3d|Sponsorship Manager|1.0|0|FALSE|2.1||Ranking and qualification of sponsor targets.
2.3|2|Draft sponsorship packages|2d|Sponsorship Manager|1.0|0|FALSE|2.2||Package structure and offer definition.
2.4|2|Finalize sponsor deck and pricing|2d|Sponsorship Manager|1.0|0|FALSE|2.3||Commercial materials for outreach.
2.5|2|Initial sponsor outreach|4d|Sponsorship Manager|1.0|0|FALSE|2.4||First-round contact campaign.
2.6|2|Follow-up sponsor outreach|3d|Sponsorship Manager|1.0|0|FALSE|2.5||Second-round sponsor follow-up.
2.7|2|Negotiate sponsor commitments|2d|Sponsorship Manager|1.0|0|FALSE|2.6||Commercial negotiation and commitment alignment.
2.8|2|Confirm sponsorship agreements|3d|Sponsorship Manager|1.0|0|FALSE|2.7||Final confirmation of sponsor support.
2.M1|2|Sponsorships confirmed|0d|||0|TRUE|2.8|2026-06-12 17:00|Funding gate milestone.
3|1|3. Marketing & Promotion||||0|FALSE|||Phase summary from the labor-cost workbook.
3.1|2|Define marketing goals and audience|2d|Marketing Lead|1.0|0|FALSE|1.M1||Target audience and messaging goals.
3.2|2|Build channel plan and message calendar|2d|Marketing Lead|1.0|0|FALSE|3.1||Campaign structure and timing.
3.3|2|Design visual identity assets|3d|Marketing Lead|1.0|1000|FALSE|3.2||Includes workbook marketing-materials cost.
3.4|2|Produce promo copy and post templates|2d|Marketing Lead|1.0|0|FALSE|3.3||Campaign content production.
3.5|2|Launch registration announcement|1d|Marketing Lead|1.0|0|FALSE|3.4||Public launch of participant recruitment.
3.6|2|Publish website and social campaign|2d|Marketing Lead|1.0|0|FALSE|3.5||Landing page and social posts published.
3.M1|2|Marketing campaign launched|0d|||0|TRUE|3.6|2026-05-29 17:00|Milestone used to open registration.
3.7|2|Promotion wave 1|5d|Marketing Lead|1.0|0|FALSE|3.M1||Early campaign push.
3.8|2|Promotion wave 2|5d|Marketing Lead|1.0|0|FALSE|3.7||Final awareness push before the event.
4|1|4. Registration & Team Formation||||0|FALSE|||Phase summary from the labor-cost workbook.
4.1|2|Configure registration form|1d|Event Coordinator|1.0|0|FALSE|1.M1||Registration workflow setup.
4.2|2|Test registration workflow|1d|Event Coordinator|1.0|0|FALSE|4.1||System test for participant signup flow.
4.3|2|Publish registration portal|1d|Event Coordinator|1.0|0|FALSE|4.2,3.M1||Registration opens once setup and marketing launch are complete.
4.M0|2|Registration open|0d|||0|TRUE|4.3|2026-05-30 09:00|Public registration milestone.
4.4|2|Monitor registrations|4d|Event Coordinator|1.0|0|FALSE|4.M0||Daily monitoring of signups and participant flow.
4.5|2|Respond to participant inquiries|3d|Event Coordinator|1.0|0|FALSE|4.4||Participant communications and issue resolution.
4.6|2|Validate participant list|3d|Event Coordinator|1.0|0|FALSE|4.5||Roster cleanup and eligibility checks.
4.7|2|Support team matching|2d|Event Coordinator|1.0|0|FALSE|4.6||Assistance for team formation and balancing.
4.8|2|Finalize team roster|3d|Event Coordinator|1.0|0|FALSE|4.7||Final list of teams and participants.
4.M1|2|Registration closed and teams formed|0d|||0|TRUE|4.8|2026-06-26 17:00|Readiness gate for final logistics.
5|1|5. Logistics & Preparation||||0|FALSE|||Phase summary from the labor-cost workbook.
5.1|2|Confirm venue availability|1d|Logistics Coordinator|1.0|0|FALSE|1.M1||Venue availability check.
5.2|2|Sign venue contract|2d|Logistics Coordinator|1.0|2000|FALSE|5.1||Includes workbook venue-rental cost.
5.3|2|Define equipment list and vendor needs|2d|Logistics Coordinator|1.0|0|FALSE|5.2||Equipment requirements definition.
5.4|2|Arrange equipment rental|3d|Logistics Coordinator|1.0|1500|FALSE|5.3||Includes workbook equipment-rental cost.
5.5|2|Collect catering quotes|1d|Logistics Coordinator|1.0|0|FALSE|5.4||Catering vendor selection input.
5.6|2|Confirm catering order|1d|Logistics Coordinator|1.0|0|FALSE|5.5||Catering order placed.
5.7|2|Final catering count and confirmation|1d|Logistics Coordinator|1.0|3000|FALSE|5.6,4.M1||Includes workbook catering cost and final attendee count.
5.8|2|Build detailed run-of-show|2d|Project Manager|1.0|0|FALSE|2.M1,4.M1||Integrated event-day operational flow.
5.9|2|Conduct readiness and quality review|1d|Project Manager|1.0|0|FALSE|5.8,5.7,5.4||Quality checkpoint before go-live.
5.10|2|Finalize integrated event schedule|1d|Project Manager|1.0|0|FALSE|5.9,3.8||Final coordination of all workstreams.
5.M1|2|Go-live readiness approved|0d|||0|TRUE|5.10|2026-07-09 17:00|Final gate before event execution.
6|1|6. Event Execution||||0|FALSE|||Phase summary from the labor-cost workbook.
6.M0|2|Hackathon kickoff|0d|||0|TRUE|5.M1|2026-07-10 08:00|Event start milestone.
6.1|2|Opening session and rules briefing|1d|Project Manager|1.25|0|FALSE|6.M0||Day 1 project leadership activity.
6.2|2|On-site branding and live coverage day 1|1d|Marketing Lead|1.0|0|FALSE|6.M0||Day 1 communications activity.
6.3|2|Sponsor reception and VIP support day 1|1d|Sponsorship Manager|0.75|0|FALSE|6.M0||Day 1 sponsor management activity.
6.4|2|Participant check-in and help desk day 1|1d|Event Coordinator|1.25|0|FALSE|6.M0||Day 1 participant operations.
6.5|2|Venue operations day 1|1d|Logistics Coordinator|1.25|0|FALSE|6.M0||Day 1 logistics operations.
6.6|2|Technical setup and help desk day 1|1d|Technical Support|1.25|0|FALSE|6.M0||Day 1 technical operations.
6.M1|2|Day 1 complete|0d|||0|TRUE|6.1,6.2,6.3,6.4,6.5,6.6|2026-07-10 20:00|End-of-day milestone.
6.7|2|Judge coordination and final decisions|1d|Project Manager|1.25|0|FALSE|6.M1||Day 2 project leadership activity.
6.8|2|Live content coverage day 2|1d|Marketing Lead|1.0|0|FALSE|6.M1||Day 2 communications activity.
6.9|2|Sponsor relations and stage support day 2|1d|Sponsorship Manager|0.75|0|FALSE|6.M1||Day 2 sponsor management activity.
6.10|2|Team support and final presentations|1d|Event Coordinator|1.25|0|FALSE|6.M1||Day 2 participant operations.
6.11|2|Venue turnover and teardown|1d|Logistics Coordinator|1.25|0|FALSE|6.M1||Day 2 logistics operations.
6.12|2|Technical judging support day 2|1d|Technical Support|1.25|0|FALSE|6.M1||Day 2 technical operations.
6.M2|2|Judging completed|0d|||0|TRUE|6.7,6.8,6.9,6.10,6.11,6.12|2026-07-11 17:00|Decision milestone before awards.
6.M3|2|Award prizes and close ceremony|0d|||2500|TRUE|6.M2|2026-07-11 18:00|Includes workbook prizes cost.
7|1|7. Closure||||0|FALSE|||Phase summary from the labor-cost workbook.
7.1|2|Send feedback survey|1d|Project Manager|1.0|0|FALSE|6.M3||Post-event feedback collection starts.
7.2|2|Analyze feedback results|2d|Project Manager|1.0|0|FALSE|7.1||Feedback analysis and insight capture.
7.3|2|Reconcile actual costs|2d|Project Manager|1.0|0|FALSE|7.2||Financial closeout analysis.
7.4|2|Close vendor payments and reserves|1d|Project Manager|1.0|1000|FALSE|7.3||Includes workbook miscellaneous closeout allowance.
7.5|2|Prepare lessons learned log|1d|Project Manager|1.0|0|FALSE|7.4||Closure knowledge capture.
7.6|2|Issue final project report|1d|Project Manager|1.0|0|FALSE|7.5||Formal closure report.
7.M1|2|Project closed|0d|||0|TRUE|7.6|2026-07-21 17:00|Formal closeout milestone.
'@ | ConvertFrom-Csv -Delimiter '|'

function Get-PredecessorIds {
    param(
        [string]$PredecessorKeys,
        [hashtable]$TaskMap
    )

    if ([string]::IsNullOrWhiteSpace($PredecessorKeys)) {
        return ''
    }

    $ids = foreach ($key in ($PredecessorKeys -split ',')) {
        $trimmed = $key.Trim()
        if ($trimmed) {
            $TaskMap[$trimmed].ID
        }
    }

    return ($ids -join ';')
}

if (Test-Path -LiteralPath $outputPath) {
    Remove-Item -LiteralPath $outputPath -Force
}

$app = $null

try {
    $app = New-Object -ComObject MSProject.Application
    $app.Visible = $false

    $project = $app.Projects.Add()
    $project.ProjectStart = $projectStart
    $project.DisplayProjectSummaryTask = $true
    $project.CurrencyCode = 'EUR'
    $project.CurrencySymbol = 'EUR'
    $project.CurrencyDigits = 2

    $projectSummary = $project.ProjectSummaryTask
    $projectSummary.Notes = 'Project summary fixed cost includes contingency before baseline and management reserve after baseline.'

    $resourceMap = @{}
    foreach ($row in $resourceRows) {
        $resource = $project.Resources.Add($row.Name)
        $resource.StandardRate = ('{0}/h' -f [double]$row.Rate)
        $resource.MaxUnits = [double]$row.MaxUnits
        $resourceMap[$row.Name] = $resource
    }

    $taskMap = @{}
    foreach ($row in $taskRows) {
        $task = $project.Tasks.Add($row.Name)
        $task.OutlineLevel = [int]$row.Level

        if (-not [string]::IsNullOrWhiteSpace($row.Duration)) {
            $task.Duration = $row.Duration
        }

        if ($row.Milestone -eq 'TRUE') {
            $task.Duration = '0d'
            $task.Milestone = $true
        }

        if ([double]$row.FixedCost -gt 0) {
            $task.FixedCost = [double]$row.FixedCost
        }

        if (-not [string]::IsNullOrWhiteSpace($row.Deadline)) {
            $task.Deadline = Get-Date $row.Deadline
        }

        if (-not [string]::IsNullOrWhiteSpace($row.Notes)) {
            $task.Notes = $row.Notes
        }

        if (-not [string]::IsNullOrWhiteSpace($row.Resource)) {
            $resource = $resourceMap[$row.Resource]
            $assignment = $task.Assignments.Add($task.ID, $resource.ID)
            if (-not [string]::IsNullOrWhiteSpace($row.Units)) {
                $assignment.Units = [double]$row.Units
            }
        }

        $taskMap[$row.Key] = $task
    }

    foreach ($row in $taskRows) {
        if (-not [string]::IsNullOrWhiteSpace($row.Predecessors)) {
            $taskMap[$row.Key].Predecessors = Get-PredecessorIds -PredecessorKeys $row.Predecessors -TaskMap $taskMap
        }
    }

    $projectSummary.FixedCost = $contingencyReserve

    $app.ViewApply('Gantt Chart') | Out-Null
    $app.GanttBarStyleCritical($true) | Out-Null
    $app.GanttBarStyleBaseline(0, $true) | Out-Null

    $app.BaselineSave($true) | Out-Null

    $projectSummary.FixedCost = $contingencyReserve + $managementReserve

    $app.FileSaveAs($outputPath) | Out-Null
    $app.FileCloseAllEx(0) | Out-Null
    $app.Quit(0)

    Write-Output "MPP generated: $outputPath"
}
finally {
    if ($app -ne $null) {
        try {
            $app.Quit(0)
        }
        catch {
        }
    }
}
