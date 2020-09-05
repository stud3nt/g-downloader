import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DownloadedListComponent } from './downloaded-list.component';

describe('DownloadedListComponent', () => {
  let component: DownloadedListComponent;
  let fixture: ComponentFixture<DownloadedListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DownloadedListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DownloadedListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
