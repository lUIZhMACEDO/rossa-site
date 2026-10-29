<?php

namespace App\Http\Controllers;

class TeamPageController extends Controller
{
    public function index()
    {
        $teamMembers = [
            'skeletal_biology' => [
                [
                    'name' => 'Peter Maye',
                    'institution' => 'University of Connecticut Health',
                    'department' => 'The Center for Regenerative Medicine and Skeletal Development, School of Dental Medicine',
                    'location' => 'Farmington, CT USA',
                ],
                [
                    'name' => 'Benjamin Sinder',
                    'institution' => 'University of Connecticut Health',
                    'department' => 'Department of Orthopedic Surgery, School of Medicine',
                    'location' => 'Farmington, CT USA',
                ],
                [
                    'name' => 'David Rowe',
                    'institution' => 'University of Connecticut Health',
                    'department' => 'The Center for Regenerative Medicine and Skeletal Development, School of Dental Medicine',
                    'location' => 'Farmington, CT USA',
                ],
                [
                    'name' => 'Thomas Andersen',
                    'institution' => 'University of Southern Denmark',
                    'department' => 'Molecular Bone Histology Lab (MBH)',
                    'location' => 'Odense, Denmark',
                ],
                [
                    'name' => 'Je-Yong Choi',
                    'institution' => 'Kyungpook National University',
                    'department' => 'Department of Biochemistry and Cell Biology',
                    'location' => 'Daegu, Republic of Korea',
                ],
            ],
            'computer_science' => [
                [
                    'name' => 'Joel Salisbury',
                    'institution' => 'University of Connecticut',
                    'department' => 'Institutional Insights & Innovation, UConn',
                    'location' => 'Storrs, CT USA',
                ],
                [
                    'name' => 'Brian Kelleher',
                    'institution' => 'UConn Institutional Insights & Innovation',
                    'department' => 'Institutional Insights & Innovation, UConn',
                    'location' => 'Storrs, CT USA',
                ],
                [
                    'name' => 'Jonathan Ameri',
                    'institution' => 'UConn Institutional Insights & Innovation',
                    'department' => 'Institutional Insights & Innovation, UConn',
                    'location' => 'Storrs, CT USA',
                ],
                [
                    'name' => 'Will Shostak',
                    'institution' => 'UConn Institutional Insights & Innovation',
                    'department' => 'Institutional Insights & Innovation, UConn',
                    'location' => 'Storrs, CT USA',
                ],
                [
                    'name' => 'Aaron Mark',
                    'institution' => 'UConn Institutional Insights & Innovation',
                    'department' => 'Institutional Insights & Innovation, UConn',
                    'location' => 'Storrs, CT USA',
                ],
                [
                    'name' => 'Natalie Lacroix',
                    'institution' => 'UConn Institutional Insights & Innovation',
                    'department' => 'Institutional Insights & Innovation, UConn',
                    'location' => 'Storrs, CT USA',
                ],
                [
                    'name' => 'Dong-Guk Shin',
                    'institution' => 'UConn Department of Computer Science',
                    'department' => 'Department of Computer Science, UConn',
                    'location' => 'Storrs, CT USA',
                ],
                [
                    'name' => 'Ion Moraru',
                    'institution' => 'University of Connecticut Health',
                    'department' => 'High Performance Computing Facility',
                    'location' => 'Farmington, CT USA',
                ],
                [
                    'name' => 'Michael Wilson',
                    'institution' => 'University of Connecticut Health',
                    'department' => 'High Performance Computing Facility',
                    'location' => 'Farmington, CT USA',
                ],
                [
                    'name' => 'Jim Schaff',
                    'institution' => 'University of Connecticut Health',
                    'department' => 'High Performance Computing Facility',
                    'location' => 'Farmington, CT USA',
                ],
            ],
            'past_contributors' => [
                [
                    'name' => 'Viktoria Bonne Kofod',
                    'institution' => 'University of Southern Denmark',
                    'department' => 'Molecular Bone Histology Lab (MBH)',
                    'location' => 'Odense, Denmark',
                ],
                [
                    'name' => 'Chenyu Zhang',
                    'institution' => 'UConn Department of Computer Science',
                    'department' => 'Department of Computer Science, UConn',
                    'location' => 'Storrs, CT USA',
                ],
            ],
        ];

        return view('about.teampage', compact('teamMembers'));
    }
}
